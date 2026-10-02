<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

/**
 * Standalone and linked invoicing.
 * Converts a prescription to a sale, or creates a direct POS sale.
 */
class SaleService
{
    public function __construct(
        protected StockService $stockService,
        protected CashService $cashService
    ) {}

    /**
     * Convert a prescription into a sale (prescription-to-sale conversion).
     */
    public function createFromPrescription(Prescription $prescription, array $payment = []): Sale
    {
        return DB::transaction(function () use ($prescription, $payment) {
            $sale = Sale::create([
                'sale_date' => now(),
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'doctor_id' => $prescription->doctor_id,
                'customer_type' => 'patient',
                'customer_name' => $prescription->patient_name,
                'customer_phone' => $prescription->patient_phone,
                'subtotal' => $prescription->medicine_cost,
                'doctor_fee' => $prescription->doctor_fee,
                'discount' => $prescription->discount,
                'tax' => $prescription->tax,
                'total' => $prescription->total_fee,
                'paid_amount' => $payment['paid_amount'] ?? $prescription->total_fee,
                'payment_method' => $payment['method'] ?? 'cash',
                'cash_account_id' => $payment['cash_account_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($prescription->items as $rxItem) {
                $sale->items()->create([
                    'product_id' => $rxItem->product_id,
                    'batch_id' => $rxItem->batch_id,
                    'prescription_item_id' => $rxItem->id,
                    'product_name' => $rxItem->drug_name,
                    'batch_number' => $rxItem->batch?->batch_number,
                    'quantity' => $rxItem->quantity,
                    'unit_price' => $rxItem->unit_price,
                    'discount' => $rxItem->discount,
                    'total' => $rxItem->total,
                ]);
                app(InventoryMovementLedger::class)->saleLink($sale, $rxItem, $prescription);
            }

            $sale->update([
                'due_amount' => max(0, $sale->total - $sale->paid_amount),
                'payment_status' => $this->resolveStatus($sale->total, $sale->paid_amount),
            ]);

            // Record cash transaction
            if ($sale->paid_amount > 0 && $sale->cash_account_id) {
                $this->cashService->recordIncome(
                    accountId: $sale->cash_account_id,
                    amount: (float) $sale->paid_amount,
                    source: $sale,
                    note: "Sale {$sale->invoice_number}"
                );
            }

            $prescription->update([
                'sale_status' => $sale->due_amount > 0 ? 'partial' : 'sold',
                'paid_amount' => $sale->paid_amount,
                'due_amount' => $sale->due_amount,
                'payment_status' => $sale->payment_status,
                'payment_method' => $sale->payment_method,
            ]);

            return $sale;
        });
    }

    /**
     * Create a direct POS / over-the-counter sale.
     */
    public function createDirectSale(array $data, array $items): Sale
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            $sale = Sale::create([
                'sale_date' => $data['sale_date'] ?? now(),
                'customer_type' => $data['customer_type'] ?? 'walking',
                'customer_name' => $data['customer_name'] ?? 'Walking Customer',
                'customer_phone' => $data['customer_phone'] ?? null,
                'patient_id' => $data['patient_id'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'doctor_fee' => $data['doctor_fee'] ?? 0,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $line) {
                $deductions = $this->stockService->deductStock($line['product_id'], $line['quantity'], [
                    'kind' => 'sale', 'source_id' => $sale->id,
                    'reference' => $sale->invoice_number ?: 'SALE-'.$sale->id, 'document_date' => $sale->sale_date,
                ]);
                $batch = $deductions[0] ?? null;

                $unitPrice = (float) $line['unit_price'];
                $qty = (float) $line['quantity'];
                $disc = (float) ($line['discount'] ?? 0);
                $total = ($unitPrice * $qty) - $disc;
                $subtotal += $total;

                $sale->items()->create([
                    'product_id' => $line['product_id'],
                    'batch_id' => $batch['batch_id'] ?? null,
                    'product_name' => $line['product_name'] ?? '',
                    'batch_number' => $batch['batch_number'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $disc,
                    'total' => $total,
                ]);
            }

            $total = $subtotal + (float) $sale->doctor_fee - (float) $sale->discount + (float) $sale->tax;
            $paid = (float) $sale->paid_amount;

            $sale->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'due_amount' => max(0, $total - $paid),
                'change_amount' => max(0, $paid - $total),
                'payment_status' => $this->resolveStatus($total, $paid),
            ]);

            if ($paid > 0 && $sale->cash_account_id) {
                $this->cashService->recordIncome(
                    accountId: $sale->cash_account_id,
                    amount: min($paid, $total),
                    source: $sale,
                    note: "POS Sale {$sale->invoice_number}"
                );
            }

            return $sale;
        });
    }

    protected function resolveStatus(float $total, float $paid): string
    {
        if ($paid <= 0) return 'pending';
        if ($paid >= $total) return 'paid';
        return 'partial';
    }
}
