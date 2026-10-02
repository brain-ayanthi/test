<?php
namespace App\Services;

use App\Support\InventoryQuantity as Q;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Logs persisted stock mutations inside the writer's OWN transaction. No stock writes here. */
class InventoryMovementLedger
{
    public const KINDS = [
        'baseline' => 'Tracking-start snapshot', 'purchase' => 'Purchase received',
        'opening_stock' => 'Opening stock import', 'sale' => 'POS sale / stock out',
        'prescription_issue' => 'Prescription stock issued', 'prescription_edit_in' => 'Prescription edit / returned to batch',
        'prescription_edit_out' => 'Prescription edit / additional issue',
        'sale_link' => 'Prescription invoiced (no stock change)', 'adjustment' => 'Stock adjustment',
        'stock_in' => 'Other stock in', 'stock_out' => 'Other stock out',
    ];

    private function ready(): void
    {
        if (DB::transactionLevel() < 1) throw new \LogicException('Inventory recording requires the stock writer transaction.');
        if (!Schema::hasTable('inventory_movements') || !Schema::hasTable('inventory_tracking_state')
            || !DB::table('inventory_tracking_state')->where('id', 1)->exists()
            || !Schema::hasColumn('inventory_movements', 'operation_id')) {
            throw new \RuntimeException('Inventory tracking is not initialized. Run the inventory movement migration during maintenance. This stock transaction was not saved.');
        }
    }
    private function product(int $id): object
    {
        $p = DB::table('products as p')->leftJoin('units as u', 'u.id', '=', 'p.purchase_unit_id')
            ->where('p.id', $id)->first(['p.id','p.name','p.sku','p.pieces_per_purchase_unit','u.short_name as purchase_unit']);
        if (!$p) throw new \RuntimeException('Inventory movement product is missing.');
        return $p;
    }
    private function common(object $p, array $context): array
    {
        $kind = $context['kind'] ?? 'stock_out';
        if (!array_key_exists($kind, self::KINDS) || $kind === 'baseline') throw new \InvalidArgumentException('Invalid inventory movement kind.');
        return [
            'operation_id' => $context['operation_id'] ?? (string) \Illuminate\Support\Str::uuid(),
            'product_id' => $p->id, 'product_name' => $p->name, 'sku' => $p->sku,
            'purchase_unit' => $p->purchase_unit, 'pieces_per_purchase_unit' => $p->pieces_per_purchase_unit,
            'kind' => $kind, 'source_id' => $context['source_id'] ?? null,
            'source_item_id' => $context['source_item_id'] ?? null,
            'reference' => mb_substr((string) ($context['reference'] ?? self::KINDS[$kind]), 0, 255),
            'document_date' => $context['document_date'] ?? null,
            'related_reference' => isset($context['related_reference']) ? mb_substr((string) $context['related_reference'], 0, 255) : null,
            'notes' => $context['notes'] ?? null,
            'actor_id' => $context['actor_id'] ?? auth()->id(), 'actor_name' => $context['actor_name'] ?? auth()->user()?->name ?? 'System / command',
            'occurred_at' => now(),
        ];
    }
    public function record(int $batchId, $before, array $context): void
    {
        $this->ready();
        $batch = DB::table('product_batches')->where('id', $batchId)->first();
        if (!$batch) throw new \RuntimeException('Inventory movement batch is missing.');
        $from = Q::minor($before); $to = Q::minor($batch->quantity);
        if ($from === $to) return;
        DB::table('inventory_movements')->insert($this->common($this->product($batch->product_id), $context) + [
            'batch_id' => $batchId, 'batch_number' => $batch->batch_number, 'expiry_date' => $batch->expiry_date,
            'quantity_before' => Q::decimal($from), 'quantity_change' => Q::decimal($to - $from),
            'quantity_after' => Q::decimal($to), 'document_quantity' => null,
        ]);
    }
    public function prescriptionContext(int $itemId, string $kind): array
    {
        $row = DB::table('prescription_items as i')->join('prescriptions as p','p.id','=','i.prescription_id')
            ->where('i.id', $itemId)->first(['p.id','p.prescription_number']);
        if (!$row) throw new \RuntimeException('Prescription stock source is missing.');
        return ['kind' => $kind, 'source_id' => $row->id, 'source_item_id' => $itemId,
            'reference' => $row->prescription_number ?: 'RX-'.$row->id];
    }
    /** An invoice is not a second stock deduction. It is a zero-delta document event. */
    public function saleLink(object $sale, object $item, object $prescription): void
    {
        $this->ready();
        $context = [
            'kind' => 'sale_link', 'source_id' => $sale->id, 'source_item_id' => $item->id,
            'reference' => $sale->invoice_number ?: 'SALE-'.$sale->id, 'document_date' => $sale->sale_date,
            'related_reference' => $prescription->prescription_number ?: 'RX-'.$prescription->id,
            'notes' => $item->stock_deducted
                ? 'Stock was already deducted for this prescription. Invoicing did not deduct it again.'
                : 'This item was not marked deducted at invoicing. The existing sale-conversion workflow does not deduct stock; any later prescription issue is recorded separately.',
        ];
        DB::table('inventory_movements')->insert($this->common($this->product($item->product_id), $context) + [
            'batch_id' => null, 'batch_number' => null, 'expiry_date' => null,
            'quantity_before' => null, 'quantity_change' => '0.00', 'quantity_after' => null,
            'document_quantity' => $item->quantity,
        ]);
    }
}
