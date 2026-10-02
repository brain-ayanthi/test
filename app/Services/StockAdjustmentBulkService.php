<?php

namespace App\Services;

use App\Models\User;
use App\Support\StockAdjustmentAccess;
use App\Support\StockQuantity as Qty;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

/** Atomic multi-row companion to the unchanged single-adjustment service. */
class StockAdjustmentBulkService
{
    public const MAX_ITEMS = 50;

    public function __construct(private StockAdjustmentService $single) {}

    private function invalid(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }

    private function normalize(array $input): array
    {
        $top = Validator::make($input, [
            'request_id' => 'required|uuid', 'items' => 'required|array|min:1|max:'.self::MAX_ITEMS,
            'items.*' => 'required|array',
        ])->validate();
        if (!array_is_list($top['items'])) $this->invalid('items', 'Adjustments must be an ordered list.');
        $root = strtolower($top['request_id']);
        $items = []; $errors = []; $seen = [];
        foreach ($top['items'] as $i => $raw) {
            // First audit row owns the submission UUID. All other IDs are derived
            // from it, so changing membership/order cannot bypass replay checks.
            $raw['request_id'] = $i === 0 ? $root : (string) Uuid::uuid5(Uuid::NAMESPACE_URL, 'clinicms-stock-bulk-v1:'.$root.':'.$i);
            foreach (['amount', 'expected_quantity', 'notes', 'reference'] as $key) {
                if (isset($raw[$key]) && is_scalar($raw[$key])) $raw[$key] = trim((string) $raw[$key]);
            }
            try {
                $v = Validator::make($raw, $this->single->rules())->validate();
                $amount = Qty::minor($v['amount']);
                $expected = Qty::minor($v['expected_quantity'], true);
                if ($v['mode'] !== 'set' && $amount === 0) {
                    throw ValidationException::withMessages(['amount' => 'Increase/decrease must be greater than zero.']);
                }
                $bid = (int) $v['batch_id'];
                if (isset($seen[$bid])) {
                    throw ValidationException::withMessages(['batch_id' => 'This batch already appears in row '.$seen[$bid].'. Use one row per batch.']);
                }
                $seen[$bid] = $i + 1;
                $ref = trim((string) ($v['reference'] ?? ''));
                // Explicit canonical whitelist: ignore extra client fields.
                $items[] = [
                    'request_id' => $raw['request_id'], 'product_id' => (int) $v['product_id'], 'batch_id' => $bid,
                    'mode' => $v['mode'], 'amount' => Qty::decimal($amount), 'expected_quantity' => Qty::decimal($expected),
                    'revision' => $v['revision'], 'reason' => $v['reason'], 'notes' => trim($v['notes']),
                    'reference' => $ref === '' ? null : $ref, 'confirm_available' => true,
                    'confirm_expired' => (bool) ($v['confirm_expired'] ?? false),
                ];
            } catch (ValidationException $e) {
                foreach ($e->errors() as $key => $messages) $errors["items.$i.$key"] = $messages;
            }
        }
        if ($errors) throw ValidationException::withMessages($errors);
        return ['request_id' => $root, 'items' => $items];
    }

    private function replay($existing, array $ids, string $hash, User $user): ?array
    {
        if ($existing->isEmpty()) return null;
        $valid = $existing->count() === count($ids);
        foreach ($ids as $id) {
            $row = $existing->get($id);
            $valid = $valid && $row && (int) $row->user_id === (int) $user->id && hash_equals($row->request_hash, $hash);
        }
        abort_unless($valid, 409, 'This submission ID conflicts with a previous adjustment or its row list changed. No new adjustments were saved. Check the original history; do not replace an unresolved submission with a new one.');
        return ['adjustments' => collect($ids)->map(fn ($id) => $existing->get($id)), 'replayed' => true];
    }

    public function apply(User $user, array $input): array
    {
        StockAdjustmentAccess::authorize($user);
        $data = $this->normalize($input);
        $items = $data['items'];
        $ids = array_column($items, 'request_id');
        // Each audit row binds the actor, entire ordered group, and all confirmations.
        // Existing request_hash CHAR(64) is sufficient; no schema change is needed.
        $hash = hash('sha256', json_encode(['version' => 'bulk-v1', 'actor' => (int) $user->id, 'submission' => $data], JSON_THROW_ON_ERROR));
        try {
            return DB::transaction(function () use ($user, $items, $ids, $hash, $data) {
                $productIds = array_values(array_unique(array_column($items, 'product_id')));
                sort($productIds, SORT_NUMERIC);
                $products = DB::table('products')->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $batches = DB::table('product_batches')->whereIn('id', array_column($items, 'batch_id'))
                    ->orderBy('product_id')->orderBy('expiry_date')->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                // Current locking read AFTER stock serialization: no stale RR snapshot.
                $existing = DB::table('stock_adjustments')->whereIn('request_id', $ids)->orderBy('request_id')->lockForUpdate()->get()->keyBy('request_id');
                if ($replay = $this->replay($existing, $ids, $hash, $user)) return $replay;
                $errors = []; $plans = [];
                foreach ($items as $i => $item) {
                    try {
                        $product = $products->get($item['product_id']);
                        $batch = $batches->get($item['batch_id']);
                        if (!$product || $product->deleted_at !== null) $this->invalid('product_id', 'Product is missing or archived.');
                        if (!$batch || (int) $batch->product_id !== (int) $product->id) $this->invalid('batch_id', 'Select a batch belonging to this product.');
                        $before = Qty::minor($batch->quantity, true);
                        if ($before !== Qty::minor($item['expected_quantity'], true) || !hash_equals($this->single->revision($batch, $product), $item['revision'])) {
                            $this->invalid('revision', 'Stock or batch details changed. Refresh and review this row. Nothing in this submission was saved.');
                        }
                        $expired = (string) $batch->expiry_date < now()->toDateString();
                        if ($expired && !$item['confirm_expired']) $this->invalid('confirm_expired', 'Confirm the expired batch. Its stock remains unavailable for normal dispensing.');
                        $amount = Qty::minor($item['amount']);
                        $after = match ($item['mode']) {'set' => $amount, 'increase' => $before + $amount, 'decrease' => $before - $amount};
                        if ($after < 0 || $after > Qty::MAX_MINOR) $this->invalid('amount', 'Result must be between 0 and 9,999,999,999.99.');
                        if ($after === $before) $this->invalid('amount', 'No change. Remove this row or enter a different verified quantity.');
                        $change = $after - $before;
                        if ($change > 0 && in_array($item['reason'], ['damaged', 'expired'], true)) $this->invalid('reason', 'Damage and expiry write-offs must reduce stock.');
                        $plans[] = compact('item', 'product', 'batch', 'before', 'after', 'change', 'expired');
                    } catch (ValidationException $e) {
                        foreach ($e->errors() as $key => $messages) $errors["items.$i.$key"] = $messages;
                    }
                }
                // Validate EVERY row before the first write. The surrounding
                // transaction also rolls back any later stock/audit write failure.
                if ($errors) throw ValidationException::withMessages($errors);
                $unitIds = $products->pluck('selling_unit_id')->filter()->unique()->values()->all();
                $units = DB::table('units')->whereIn('id', $unitIds)->pluck('short_name', 'id');
                $now = now(); $roles = $user->getRoleNames()->join(', '); $saved = [];
                // Product-wide running totals. Read under the product locks that
                // already serialise this submission, and advanced as each row of
                // the same submission is applied, so one multi-row submission
                // reports the true before/after for every product.
                $allBatches = DB::table('product_batches')->whereIn('product_id', $productIds)
                    ->orderBy('id')->lockForUpdate()->get(['product_id', 'quantity']);
                $productTotals = [];
                foreach ($allBatches as $b) {
                    $pid = (int) $b->product_id;
                    $productTotals[$pid] = ($productTotals[$pid] ?? 0) + Qty::minor($b->quantity, true);
                }
                foreach ($plans as $plan) {
                    ['item' => $item, 'product' => $product, 'batch' => $batch, 'before' => $before, 'after' => $after, 'change' => $change, 'expired' => $expired] = $plan;
                    $productBefore = $productTotals[(int) $product->id] ?? 0;
                    DB::table('product_batches')->where('id', $batch->id)->update(['quantity' => Qty::decimal($after), 'updated_at' => $now]);
                    $productTotals[(int) $product->id] = $productBefore + $change;
                    $id = DB::table('stock_adjustments')->insertGetId([
                        'request_id' => $item['request_id'], 'request_hash' => $hash,
                        'product_quantity_before' => Qty::decimal($productBefore),
                        'product_quantity_after' => Qty::decimal($productBefore + $change),
                        'product_id' => $product->id, 'batch_id' => $batch->id, 'user_id' => $user->id,
                        'product_name' => $product->name, 'sku' => $product->sku,
                        'batch_number' => $batch->batch_number, 'expiry_date' => $batch->expiry_date,
                        'stock_unit' => $units->get($product->selling_unit_id) ?: 'stock units',
                        'user_name' => $user->name, 'user_roles' => $roles,
                        'mode' => $item['mode'], 'entered_quantity' => $item['amount'],
                        'quantity_before' => Qty::decimal($before), 'quantity_change' => Qty::decimal($change), 'quantity_after' => Qty::decimal($after),
                        'reason' => $item['reason'], 'notes' => $item['notes'], 'reference' => $item['reference'],
                        'expired_confirmed' => $expired && $item['confirm_expired'], 'created_at' => $now,
                    ]);
                    app(InventoryMovementLedger::class)->record($batch->id, Qty::decimal($before), [
                        'operation_id' => $data['request_id'],
                        'actor_id' => $user->id, 'actor_name' => $user->name,
                        'kind' => 'adjustment', 'source_id' => $id, 'reference' => 'ADJ-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT),
                        'related_reference' => $item['reference'], 'notes' => $item['reason'].': '.$item['notes'],
                    ]);
                    $saved[] = $id;
                }
                return ['adjustments' => DB::table('stock_adjustments')->whereIn('id', $saved)->orderBy('id')->get(), 'replayed' => false];
            }, 3);
        } catch (QueryException $e) {
            // Handles a competing UUID on different products after rollback.
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                $existing = DB::table('stock_adjustments')->whereIn('request_id', $ids)->get()->keyBy('request_id');
                if ($replay = $this->replay($existing, $ids, $hash, $user)) return $replay;
            }
            throw $e;
        }
    }
}
