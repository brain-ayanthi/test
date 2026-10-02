<?php

namespace App\Services;

use App\Models\User;
use App\Support\StockAdjustmentAccess;
use App\Support\StockQuantity as Qty;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public const REASONS = [
        'physical_count' => 'Physical count / stocktake',
        'damaged' => 'Damaged stock',
        'expired' => 'Expired stock write-off',
        'data_correction' => 'Data entry correction',
        'found_stock' => 'Previously unrecorded stock',
        'other' => 'Other (explain in notes)',
    ];

    public function rules(): array
    {
        return [
            'request_id' => 'required|uuid',
            'product_id' => 'required|integer|min:1',
            'batch_id' => 'required|integer|min:1',
            'mode' => 'required|in:set,increase,decrease',
            'amount' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'expected_quantity' => ['required', 'regex:/^-?\d{1,10}(?:\.\d{1,2})?$/D'],
            'revision' => ['required', 'regex:/^[a-f0-9]{64}$/D'],
            'reason' => 'required|in:'.implode(',', array_keys(self::REASONS)),
            'notes' => 'required|string|min:3|max:2000',
            'reference' => 'nullable|string|max:100',
            'confirm_available' => 'required|accepted',
            'confirm_expired' => 'nullable|boolean',
        ];
    }

    public function revision(object $batch, object $product): string
    {
        $key = (string) config('app.key');
        if ($key === '') throw new \RuntimeException('APP_KEY must be configured before stock adjustments are enabled.');
        return hash_hmac('sha256', json_encode([
            'batch' => (int) $batch->id, 'product' => (int) $batch->product_id,
            'quantity' => Qty::decimal(Qty::minor($batch->quantity, true)),
            'batch_number' => $batch->batch_number, 'expiry_date' => (string) $batch->expiry_date,
            'updated_at' => (string) $batch->updated_at,
            'product_name' => $product->name, 'sku' => $product->sku,
            'unit_id' => $product->selling_unit_id, 'product_updated_at' => (string) $product->updated_at,
            'product_active' => (bool) $product->is_active,
        ], JSON_THROW_ON_ERROR), $key);
    }

    private function invalid(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }

    private function replay(object $row, User $user, string $fingerprint): array
    {
        abort_unless((int) $row->user_id === (int) $user->id && hash_equals($row->request_hash, $fingerprint),
            409, 'This request ID was already used for another operation. Review the adjustment history; do not resubmit it with different values.');
        return ['adjustment' => $row, 'replayed' => true];
    }

    public function apply(User $user, array $input): array
    {
        StockAdjustmentAccess::authorize($user);
        // Validate here too, so another caller cannot bypass controller validation.
        foreach (['amount', 'expected_quantity', 'notes', 'reference', 'request_id'] as $field) {
            if (isset($input[$field]) && is_scalar($input[$field])) $input[$field] = trim((string) $input[$field]);
        }
        $data = Validator::make($input, $this->rules())->validate();
        $amount = Qty::minor($data['amount']);
        $expected = Qty::minor($data['expected_quantity'], true);
        if ($data['mode'] !== 'set' && $amount === 0) $this->invalid('amount', 'Increase/decrease quantity must be greater than zero.');
        $data['notes'] = trim($data['notes']);
        $reference = trim((string) ($data['reference'] ?? ''));
        $data['reference'] = $reference === '' ? null : $reference;
        $data['request_id'] = strtolower($data['request_id']);
        $data['confirm_expired'] = (bool) ($data['confirm_expired'] ?? false);
        $fingerprint = hash('sha256', json_encode([
            'user' => (int) $user->id, 'product' => (int) $data['product_id'], 'batch' => (int) $data['batch_id'],
            'mode' => $data['mode'], 'amount' => Qty::decimal($amount), 'expected' => Qty::decimal($expected),
            'revision' => $data['revision'], 'reason' => $data['reason'], 'notes' => $data['notes'],
            'reference' => $data['reference'], 'expired_confirmed' => $data['confirm_expired'],
        ], JSON_THROW_ON_ERROR));

        try {
            return DB::transaction(function () use ($user, $data, $amount, $expected, $fingerprint) {
                // Current locking read makes an identical retry safe even after later
                // stock movements. Product/batch locks serialize adjustments and counts.
                $product = DB::table('products')->where('id', $data['product_id'])->lockForUpdate()->first();
                if (!$product) $this->invalid('product_id', 'Product not found.');
                $batch = DB::table('product_batches')->where('id', $data['batch_id'])->lockForUpdate()->first();
                if (!$batch || (int) $batch->product_id !== (int) $product->id) {
                    $this->invalid('batch_id', 'Select a batch that belongs to the selected product.');
                }
                $existing = DB::table('stock_adjustments')->where('request_id', $data['request_id'])->lockForUpdate()->first();
                if ($existing) return $this->replay($existing, $user, $fingerprint);
                if ($product->deleted_at !== null) $this->invalid('product_id', 'Archived products cannot receive new adjustments.');
                $before = Qty::minor($batch->quantity, true);
                if ($before !== $expected || !hash_equals($this->revision($batch, $product), $data['revision'])) {
                    $this->invalid('revision', 'Stock or batch details changed after you loaded them. Refresh batches, recheck the count and confirm again. No adjustment was saved.');
                }
                $expired = (string) $batch->expiry_date < now()->toDateString();
                if ($expired && !$data['confirm_expired']) {
                    $this->invalid('confirm_expired', 'Confirm that this batch is expired. Its stock remains unavailable for normal dispensing.');
                }
                $after = match ($data['mode']) {
                    'set' => $amount,
                    'increase' => $before + $amount,
                    'decrease' => $before - $amount,
                };
                if ($after < 0) $this->invalid('amount', 'This adjustment would make stock negative. Nothing was saved.');
                if ($after > Qty::MAX_MINOR) $this->invalid('amount', 'Resulting stock exceeds the database quantity limit.');
                if ($after === $before) $this->invalid('amount', 'The new quantity is the same as the current quantity. No adjustment is needed.');
                $change = $after - $before;
                if (in_array($data['reason'], ['damaged', 'expired'], true) && $change > 0) {
                    $this->invalid('reason', 'Damage and expiry write-offs must reduce stock. Choose an appropriate reason for an increase.');
                }
                $unit = $product->selling_unit_id
                    ? DB::table('units')->where('id', $product->selling_unit_id)->value('short_name') : null;
                // Product-wide totals, read under the product lock that serialises
                // adjustments for this product. Recorded basis matches the batch
                // figures shown on the screen (expired/negative batches included).
                $productRows = DB::table('product_batches')->where('product_id', $product->id)
                    ->lockForUpdate()->get(['quantity']);
                $productTotalBefore = (int) $productRows->sum(fn ($b) => Qty::minor($b->quantity, true));
                $now = now();
                DB::table('product_batches')->where('id', $batch->id)->update([
                    'quantity' => Qty::decimal($after), 'updated_at' => $now,
                ]);
                $id = DB::table('stock_adjustments')->insertGetId([
                    'request_id' => $data['request_id'], 'request_hash' => $fingerprint,
                    'product_quantity_before' => Qty::decimal($productTotalBefore),
                    'product_quantity_after' => Qty::decimal($productTotalBefore + $change),
                    'product_id' => $product->id, 'batch_id' => $batch->id, 'user_id' => $user->id,
                    'product_name' => $product->name, 'sku' => $product->sku,
                    'batch_number' => $batch->batch_number, 'expiry_date' => $batch->expiry_date,
                    'stock_unit' => $unit ?: 'stock units', 'user_name' => $user->name,
                    'user_roles' => $user->getRoleNames()->join(', '),
                    'mode' => $data['mode'], 'entered_quantity' => Qty::decimal($amount),
                    'quantity_before' => Qty::decimal($before), 'quantity_change' => Qty::decimal($change),
                    'quantity_after' => Qty::decimal($after), 'reason' => $data['reason'],
                    'notes' => $data['notes'], 'reference' => $data['reference'],
                    'expired_confirmed' => $expired && $data['confirm_expired'], 'created_at' => $now,
                ]);
                app(InventoryMovementLedger::class)->record($batch->id, Qty::decimal($before), [
                    'actor_id' => $user->id, 'actor_name' => $user->name,
                    'kind' => 'adjustment', 'source_id' => $id, 'reference' => 'ADJ-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT),
                    'related_reference' => $data['reference'], 'notes' => $data['reason'].': '.$data['notes'],
                ]);
                return ['adjustment' => DB::table('stock_adjustments')->where('id', $id)->first(), 'replayed' => false];
            }, 3);
        } catch (QueryException $e) {
            // A concurrent identical UUID on another connection can win the UNIQUE
            // insert race. The failed transaction has rolled back before this lookup.
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                $existing = DB::table('stock_adjustments')->where('request_id', $data['request_id'])->first();
                if ($existing) return $this->replay($existing, $user, $fingerprint);
            }
            throw $e;
        }
    }
}
