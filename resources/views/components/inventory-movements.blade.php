@php $showProduct = !isset($product); @endphp
<p class="iv-muted" style="margin:12px 0">Before and balance are <strong>product-total ledger stock</strong> across all batches, including expired stock—not a single batch or available-only balance. Rows are shown newest first. Filters do not reset balances. Only Prescription, Purchase Invoice and Stock Adjustment changes are listed; other stock changes still count toward balances.</p>
<div class="iv-scroll"><table data-product-history="v2"><thead><tr><th>Date / time</th>@if($showProduct)<th>Product / SKU</th>@endif<th>Transaction</th><th>Reference</th><th class="num">Before stock (pcs)</th><th class="num">Added (pcs)</th><th class="num">Reduced (pcs)</th><th class="num">Balance stock (pcs)</th></tr></thead><tbody>
@forelse($movements as $m)
<tr>
 <td>{{ \Illuminate\Support\Carbon::parse($m->occurred_at)->format('Y-m-d H:i:s') }}</td>
 @if($showProduct)<td>@if($m->product_deleted_at === null)<a class="iv-link" href="{{ route('products.show',$m->product_id) }}">{{ $m->product_name }}</a>@else{{ $m->product_name }}@endif<small>{{ $m->sku }}</small></td>@endif
 <td>{{ match($m->kind) { 'purchase'=>'Purchase Invoice', 'adjustment'=>'Stock Adjustment', 'prescription_edit_in'=>'Prescription — edit return', 'prescription_edit_out'=>'Prescription — additional issue', default=>'Prescription' } }}</td>
 <td>{{ $m->reference }}@if($m->related_reference)<small>{{ $m->related_reference }}</small>@endif</td>
 <td class="num">{{ \App\Support\InventoryQuantity::display($m->product_before) }}</td>
 <td class="num positive">{{ \App\Support\InventoryQuantity::display($m->quantity_in) }}</td>
 <td class="num negative">{{ \App\Support\InventoryQuantity::display($m->quantity_out) }}</td>
 <td class="num"><strong>{{ \App\Support\InventoryQuantity::display($m->product_after) }}</strong></td>
</tr>
@empty<tr><td colspan="{{ $showProduct ? 8 : 7 }}" class="empty">No Prescription, Purchase Invoice or Stock Adjustment changes match these filters.</td></tr>@endforelse
</tbody></table></div>
{{ $movements->onEachSide(2)->links('components.inventory-pagination') }}
