@extends('layouts.app')
@section('title', $product->name.' — Stock History')
@section('page-title', 'Product Stock History')
@include('components.inventory-styles')
@section('content')
<div class="iv" data-inventory-version="product-total-v2">
 <div class="iv-head"><div><h2>{{ $product->name }}</h2><p class="iv-muted">{{ $product->sku }} · {{ $product->category_name ?? 'Uncategorized' }}</p></div><div class="iv-tabs"><a class="iv-btn" href="{{ route('products.index') }}">Back to inventory</a><a class="iv-btn" href="{{ route('reports.inventory',['product_id'=>$product->id]) }}">Inventory Report</a></div></div>
 @if($errors->any())<div class="iv-errors">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
 <section class="iv-card"><div class="iv-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
  <div class="iv-stat"><small>Available stock (pcs)</small><strong>{{ \App\Support\InventoryQuantity::display($product->available_pcs) }}</strong></div>
  <div class="iv-stat"><small>Available stock (Purchase Unit)</small><strong style="font-size:19px">{{ \App\Support\InventoryQuantity::packs($product->available_pcs,$product->pieces_per_purchase_unit,$product->purchase_unit) }}</strong></div>
  <div class="iv-stat"><small>Total recorded stock (all batches, pcs)</small><strong>{{ \App\Support\InventoryQuantity::display($product->recorded_pcs) }}</strong></div>
 </div></section>
 @if($product->mismatched_batches>0)<div class="iv-info iv-warn">The tracked balance differs from current recorded stock. A stock change outside tracking may have occurred; review it before relying on this history.</div>@endif
 <section class="iv-card"><div class="iv-head"><h3>Product stock history</h3><a class="iv-btn" href="{{ route('products.show',$product->id) }}">Reset</a></div>
 <p class="iv-muted">History from {{ $state->started_at }} ({{ config('app.timezone') }}). Prescription · Purchase Invoice · Stock Adjustment.</p>
 <form method="GET" action="{{ route('products.show',$product->id) }}">@include('components.inventory-movement-filters')</form>
 @include('components.inventory-movements')
 </section>
</div>
@endsection
