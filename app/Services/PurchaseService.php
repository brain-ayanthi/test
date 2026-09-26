<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

/**
 * Handles purchase creation with box-to-piece conversion.
 * Purchase may be in Boxes (e.g. 1 box = 1000 pieces);
 * prescription/sales always transact in pieces.
 */
class PurchaseService
{
    public function __construct(protected StockService $stockService) {}

    /**
     * Create a purchase invoice and automatically update stock.
     *
     * @param array $data
     * @param array $items [['product_id','purchase_quantity','pieces_per_unit','purchase_price','selling_price','batch_number','expiry_date'], ...]
     */
    public function createPurchase(array $data, array $items): Purchase
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;

            $purchase = Purchase::create([
                'invoice_number' => $data['invoice_number'] ?? null,
                'vendor_id' => $data['vendor_id'],
                'purchase_date' => $data['purchase_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'reference' => $data['reference'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'shipping' => $data['shipping'] ?? 0,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'paid_amount' => $data['paid_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $line) {
                $product = Product::findOrFail($line['product_id']);

                $purchaseQty = (float) $line['purchase_quantity'];       // e.g. 5 boxes
                $piecesPerUnit = (float) ($line['pieces_per_unit'] ?? 1); // e.g. 1000 pieces per box
                $totalPieces = $purchaseQty * $piecesPerUnit;              // 5000 pieces

                $purchasePrice = (float) $line['purchase_price'];         // per box
                $unitCost = $piecesPerUnit > 0 ? $purchasePrice / $piecesPerUnit : 0; // per piece
                $sellingPrice = (float) ($line['selling_price'] ?? $product->selling_price);

                $lineDiscount = (float) ($line['discount'] ?? 0);
                $lineTax = (float) ($line['tax'] ?? 0);
                $lineTotal = ($purchasePrice * $purchaseQty) - $lineDiscount + $lineTax;
                $subtotal += $lineTotal;

                $item = $purchase->items()->create([
                    'product_id' => $product->id,
                    'purchase_quantity' => $purchaseQty,
                    'purchase_unit_id' => $line['purchase_unit_id'] ?? $product->purchase_unit_id,
                    'pieces_per_unit' => $piecesPerUnit,
                    'total_pieces' => $totalPieces,
                    'purchase_price' => $purchasePrice,
                    'unit_cost' => $unitCost,
                    'selling_price' => $sellingPrice,
                    'batch_number' => $line['batch_number'] ?? null,
                    'expiry_date' => $line['expiry_date'] ?? null,
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                ]);

                // Update product master selling price if supplied
                if ($sellingPrice > 0) {
                    $product->update(['selling_price' => $sellingPrice]);
                }

                // Add stock (in pieces) via FEFO batch system
                if ($totalPieces > 0 && !empty($line['batch_number'])) {
                    $this->stockService->addStock(
                        productId: $product->id,
                        quantity: $totalPieces,
                        batchNumber: $line['batch_number'],
                        expiryDate: $line['expiry_date'] ?? now()->addYear(),
                        purchasePrice: $unitCost,
                        sellingPrice: $sellingPrice,
                        purchaseId: $purchase->id,
                        purchaseItemId: $item->id
                    );
                }
            }

            $total = $subtotal - (float) ($data['discount'] ?? 0)
                     + (float) ($data['tax'] ?? 0)
                     + (float) ($data['shipping'] ?? 0);

            $paidAmount = (float) ($data['paid_amount'] ?? 0);
            $purchase->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'due_amount' => max(0, $total - $paidAmount),
                'payment_status' => $this->resolvePaymentStatus($total, $paidAmount),
            ]);

            return $purchase;
        });
    }

    protected function resolvePaymentStatus(float $total, float $paid): string
    {
        if ($paid <= 0) return 'pending';
        if ($paid >= $total) return 'paid';
        return 'partial';
    }
}
