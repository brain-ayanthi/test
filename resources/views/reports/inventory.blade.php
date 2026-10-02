@extends('layouts.app')
@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')
@include('components.inventory-styles')
@section('content')
<div class="iv" data-inventory-version="product-total-v2">
 <div class="iv-head"><h2>Inventory Report</h2><div class="iv-tabs"><a class="iv-btn" href="{{ route('reports.index') }}">All reports</a><a class="iv-btn" href="{{ route('products.index') }}">Product inventory</a></div></div>
 @if($errors->any())<div class="iv-errors">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
 <nav class="iv-tabs"><a class="iv-btn {{ $tab==='stock'?'iv-active':'' }}" href="{{ route('reports.inventory',array_filter(['tab'=>'stock','product_id'=>request('product_id')])) }}">Current stock</a><a class="iv-btn {{ $tab==='movements'?'iv-active':'' }}" href="{{ route('reports.inventory',array_filter(['tab'=>'movements','product_id'=>request('product_id')])) }}">Stock history</a></nav>
 <section class="iv-card">
 @if($tab==='stock')
 <h3>Current inventory</h3><p class="iv-muted">Available stock excludes expired/negative batches. Purchase-unit stock uses the same available quantity. This is current stock, not a historical closing balance.</p>
 <form method="GET" action="{{ route('reports.inventory') }}" class="iv-filter"><input type="hidden" name="tab" value="stock">
 @if(request('product_id'))<input type="hidden" name="product_id" value="{{ request('product_id') }}">@endif
 <label>Product / SKU<input name="q" maxlength="100" value="{{ request('q') }}"></label>
 <label>Category<select name="category_id"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select></label>
 <label>Stock status<select name="status">@foreach(['all'=>'All','zero'=>'Zero available','low'=>'At/below minimum','available'=>'Available stock','mismatch'=>'Balance warning'] as $value=>$label)<option value="{{ $value }}" @selected(request('status','all')===$value)>{{ $label }}</option>@endforeach</select></label>
 <label>Rows<select name="per_page">@foreach([10,25,50,100] as $n)<option value="{{ $n }}" @selected((int)request('per_page',25)===$n)>{{ $n }}</option>@endforeach</select></label>
 <button class="iv-btn iv-active">Filter</button><a class="iv-btn" href="{{ route('reports.inventory') }}">Reset</a></form>
 <div class="iv-scroll"><table><thead><tr><th>Product / SKU</th><th>Purchase Unit</th><th class="num">Stock (pcs)</th><th>Stock (Purchase Unit)</th><th class="num">Total recorded stock (pcs)</th><th></th></tr></thead><tbody>
 @forelse($products as $p)
 <tr><td><strong>{{ $p->name }}</strong><small>{{ $p->sku }}</small>@if(!$p->is_active)<small>Inactive product</small>@endif</td><td>{{ $p->pieces_per_purchase_unit }} pcs / {{ $p->purchase_unit ?? 'not configured' }}</td><td class="num">{{ \App\Support\InventoryQuantity::display($p->available_pcs) }}</td><td>{{ \App\Support\InventoryQuantity::packs($p->available_pcs,$p->pieces_per_purchase_unit,$p->purchase_unit) }}</td><td class="num">{{ \App\Support\InventoryQuantity::display($p->recorded_pcs) }}@if($p->mismatched_batches>0)<small class="negative">Tracked balance differs</small>@endif</td><td><a class="iv-link" href="{{ route('products.show',$p->id) }}">View history</a></td></tr>
 @empty<tr><td colspan="6" class="empty">No products match these filters.</td></tr>@endforelse
 </tbody></table></div>{{ $products->onEachSide(2)->links('components.inventory-pagination') }}
 @else
 <div class="iv-head"><h3>Product stock history</h3><a class="iv-btn" href="{{ route('reports.inventory',['tab'=>'movements']) }}">Reset</a></div>
 <p class="iv-muted">History from {{ $state->started_at }} ({{ config('app.timezone') }}).</p>
 <form method="GET" action="{{ route('reports.inventory') }}"><input type="hidden" name="tab" value="movements">
 @if(request('product_id'))<input type="hidden" name="product_id" value="{{ request('product_id') }}">@endif
 <label class="iv-muted">Category<select name="category_id" style="border:1px solid #cbd5e1;padding:7px;border-radius:6px"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select></label>
 @include('components.inventory-movement-filters')</form>
 @include('components.inventory-movements')
 @endif
 </section>
</div>
@endsection
