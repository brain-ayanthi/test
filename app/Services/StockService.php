<?php

namespace App\Services;

use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;

/**
 * Stock management using FEFO (First Expiry First Out).
 */
class StockService
{
    /**
     * Deduct stock for MULTIPLE items in ONE transaction/lock cycle.
     * This is much faster than calling deductStock() once per item.
     *
     * @param array $items [['product_id'=>..,'quantity'=>..], ...]
     */
    public function deductMany(array $items): array
    {
        return DB::transaction(function () use ($items) {
            // Group by product and sum quantities
            $needed = [];
            foreach ($items as $it) {
                $pid = (int) $it['product_id'];
                $qty = (float) ($it['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $needed[$pid] = ($needed[$pid] ?? 0) + $qty;
            }

            if (empty($needed)) {
                return [];
            }

            // Load ALL relevant batches in ONE query with a single lock
            $batches = ProductBatch::whereIn('product_id', array_keys($needed))
                ->where('quantity', '>', 0)
                ->whereDate('expiry_date', '>=', now())
                ->orderBy('product_id')
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->get()
                ->groupBy('product_id');

            $deductions = [];
            foreach ($needed as $pid => $qty) {
                $productBatches = $batches->get($pid, collect());
                $available = $productBatches->sum('quantity');
                if ($available < $qty) {
                    throw new \Exception("Insufficient stock for product #{$pid}. Available: {$available}, requested: {$qty}");
                }

                $remaining = $qty;
                foreach ($productBatches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $take = min((float) $batch->quantity, $remaining);
                    $batch->decrement('quantity', $take);
                    $deductions[$pid] = [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity' => $take,
                    ];
                    $remaining -= $take;
                }
            }

            return $deductions;
        });
    }

    /**
     * Deduct stock for a single product (kept for backward compatibility).
     */
    public function deductStock(int $productId, float $quantity): array
    {
        $result = $this->deductMany([['product_id' => $productId, 'quantity' => $quantity]]);
        return $result ? [$result[$productId]] : [];
    }

    public function addStock(
        int $productId,
        float $quantity,
        string $batchNumber,
        $expiryDate,
        float $purchasePrice = 0,
        float $sellingPrice = 0,
        ?int $purchaseId = null,
        ?int $purchaseItemId = null
    ): ProductBatch {
        return DB::transaction(function () use (
            $productId, $quantity, $batchNumber, $expiryDate,
            $purchasePrice, $sellingPrice, $purchaseId, $purchaseItemId
        ) {
            $existing = ProductBatch::where('product_id', $productId)
                ->where('batch_number', $batchNumber)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->increment('quantity', $quantity);
                return $existing;
            }

            return ProductBatch::create([
                'product_id' => $productId,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'quantity' => $quantity,
                'initial_quantity' => $quantity,
                'purchase_id' => $purchaseId,
                'purchase_item_id' => $purchaseItemId,
            ]);
        });
    }
}
