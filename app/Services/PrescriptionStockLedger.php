<?php

namespace App\Services;

use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Exact per-item/per-batch allocation for prescriptions only. StockService is unchanged. */
class PrescriptionStockLedger
{
    public static function units($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['stock' => $message]);
    }

    /** Caller must own the prescription row lock and a DB transaction. */
    public function allocations(object $item)
    {
        $rows = DB::table('prescription_stock_allocations')
            ->where('prescription_item_id', $item->id)->orderBy('id')->lockForUpdate()->get();
        $sum = $rows->sum(fn ($row) => self::units($row->quantity));
        if (!$item->stock_deducted && $rows->isNotEmpty()) {
            $this->fail("Inconsistent stock ledger for item #{$item->id}. Administrator review required.");
        }
        if ($item->stock_deducted && $rows->isNotEmpty() && $sum !== self::units($item->quantity)) {
            $this->fail("Stock allocation mismatch for item #{$item->id}. Administrator review required.");
        }
        return $rows;
    }

    /** Used before updating an existing item. Zero quantity means removal. */
    public function change(object $item, int $productId, $quantity): void
    {
        $rows = $this->allocations($item);
        $old = self::units($item->quantity);
        $new = self::units($quantity);
        $sameProduct = (int) $item->product_id === $productId;
        if ($item->stock_deducted && $rows->isEmpty()) {
            if (!$sameProduct || $old !== $new) {
                $this->fail("Item #{$item->id} has historical stock without a batch allocation ledger. Its product/quantity cannot be changed and it cannot be removed. Other fields can be edited. Do not manually clear stock_deducted.");
            }
            return;
        }
        if (!$item->stock_deducted) {
            // Saved below as pending, then deducted once at the end of the transaction.
            return;
        }
        if (!$sameProduct) {
            $this->release($item, $rows, $old);
            if ($new > 0) $this->allocate($item->id, $productId, $new);
        } elseif ($new < $old) {
            $this->release($item, $rows, $old - $new);
        } elseif ($new > $old) {
            $this->allocate($item->id, $productId, $new - $old);
        }
        $this->refreshBatchPointer($item->id);
    }

    private function release(object $item, $rows, int $remaining): void
    {
        $context = app(InventoryMovementLedger::class)->prescriptionContext($item->id, 'prescription_edit_in')
            + ['operation_id' => (string) \Illuminate\Support\Str::uuid()];
        // Return exactly to the original batches, including batches now expired.
        foreach ($rows->reverse() as $row) {
            if ($remaining === 0) break;
            $batch = DB::table('product_batches')->where('id', $row->batch_id)->lockForUpdate()->first();
            if (!$batch || (int) $batch->product_id !== (int) $item->product_id) {
                $this->fail("Missing or mismatched original batch for item #{$item->id}.");
            }
            $held = self::units($row->quantity);
            $give = min($remaining, $held);
            DB::table('product_batches')->where('id', $batch->id)->update([
                'quantity' => (self::units($batch->quantity) + $give) / 100,
                'updated_at' => now(),
            ]);
            app(InventoryMovementLedger::class)->record($batch->id, $batch->quantity,
                $context);
            if ($give === $held) {
                DB::table('prescription_stock_allocations')->where('id', $row->id)->delete();
            } else {
                DB::table('prescription_stock_allocations')->where('id', $row->id)->update([
                    'quantity' => ($held - $give) / 100, 'updated_at' => now(),
                ]);
            }
            $remaining -= $give;
        }
        if ($remaining !== 0) $this->fail('Incomplete batch allocation; nothing has been saved.');
    }

    private function allocate(int $itemId, int $productId, int $needed, string $movementKind = 'prescription_edit_out', ?string $operationId = null): void
    {
        if ($needed <= 0) return;
        $context = app(InventoryMovementLedger::class)->prescriptionContext($itemId, $movementKind)
            + ['operation_id' => $operationId ?? (string) \Illuminate\Support\Str::uuid()];
        $batches = DB::table('product_batches')->where('product_id', $productId)
            ->where('quantity', '>', 0)->whereDate('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date')->orderBy('id')->lockForUpdate()->get();
        $available = $batches->sum(fn ($b) => self::units($b->quantity));
        if ($available < $needed) {
            $this->fail('Insufficient unexpired stock for product #'.$productId.
                '. Available: '.($available / 100).'; additional required: '.($needed / 100).'. No changes saved.');
        }
        foreach ($batches as $batch) {
            if ($needed === 0) break;
            $take = min(self::units($batch->quantity), $needed);
            DB::table('product_batches')->where('id', $batch->id)->update([
                'quantity' => (self::units($batch->quantity) - $take) / 100, 'updated_at' => now(),
            ]);
            app(InventoryMovementLedger::class)->record($batch->id, $batch->quantity,
                $context);
            $allocation = DB::table('prescription_stock_allocations')
                ->where('prescription_item_id', $itemId)->where('batch_id', $batch->id)->first();
            if ($allocation) {
                DB::table('prescription_stock_allocations')->where('id', $allocation->id)->update([
                    'quantity' => (self::units($allocation->quantity) + $take) / 100, 'updated_at' => now(),
                ]);
            } else {
                DB::table('prescription_stock_allocations')->insert([
                    'prescription_item_id' => $itemId, 'batch_id' => $batch->id,
                    'quantity' => $take / 100, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $needed -= $take;
        }
    }

    private function refreshBatchPointer(int $itemId): void
    {
        // Compatibility pointer only. The ledger is authoritative for split batches.
        $batchId = DB::table('prescription_stock_allocations')->where('prescription_item_id', $itemId)
            ->orderBy('id')->value('batch_id');
        DB::table('prescription_items')->where('id', $itemId)->update(['batch_id' => $batchId]);
    }

    /** Idempotent. Serialized with Edit Save on the same prescription row. */
    public function deductPending(Prescription $prescription): Prescription
    {
        return DB::transaction(function () use ($prescription) {
            $rx = Prescription::whereKey($prescription->id)->lockForUpdate()->firstOrFail();
            $items = DB::table('prescription_items')->where('prescription_id', $rx->id)
                ->where('stock_deducted', 0)->orderBy('product_id')->orderBy('id')->lockForUpdate()->get();
            $operationId = (string) \Illuminate\Support\Str::uuid();
            foreach ($items as $item) {
                $this->allocations($item);
                if (self::units($item->quantity) <= 0) {
                    $this->fail("Item #{$item->id} must have a positive quantity before stock deduction.");
                }
                $this->allocate($item->id, $item->product_id, self::units($item->quantity), 'prescription_issue', $operationId);
                $this->refreshBatchPointer($item->id);
                DB::table('prescription_items')->where('id', $item->id)->update([
                    'stock_deducted' => 1, 'updated_at' => now(),
                ]);
            }
            // Printing state is not changed by a background stock-only operation.
            return $rx->fresh();
        }, 3);
    }
}
