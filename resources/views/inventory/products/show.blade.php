@extends('layouts.app')
@section('title', $product->name)
@section('page-title', 'Product Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="text-xl font-bold">{{ $product->name }}</h2>
        <p class="text-gray-500">{{ $product->form_type }} {{ $product->strength }}</p>
        <p class="text-sm text-gray-400 font-mono">{{ $product->sku }}</p>
        <div class="mt-4 space-y-1 text-sm">
            <p>Generic: <strong>{{ $product->generic_name }}</strong></p>
            <p>Category: {{ $product->category?->name ?? '-' }}</p>
            <p>Selling Price: <strong class="text-green-600">Rs {{ number_format($product->selling_price,2) }}</strong></p>
            <p>Purchase Price: Rs {{ number_format($product->purchase_price,2) }}</p>
            <p>Rack: {{ $product->rack_number }}</p>
        </div>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Stock Summary (FEFO Batches)</h3>
        <div class="grid grid-cols-4 gap-3 mb-4">
            <div class="bg-green-50 p-3 rounded text-center"><div class="text-2xl font-bold text-green-600">{{ $summary['total_stock'] }}</div><div class="text-xs text-gray-500">Total Stock</div></div>
            <div class="bg-blue-50 p-3 rounded text-center"><div class="text-2xl font-bold text-blue-600">{{ $summary['available'] }}</div><div class="text-xs text-gray-500">Available</div></div>
            <div class="bg-red-50 p-3 rounded text-center"><div class="text-2xl font-bold text-red-600">{{ $summary['expired'] }}</div><div class="text-xs text-gray-500">Expired</div></div>
            <div class="bg-amber-50 p-3 rounded text-center"><div class="text-2xl font-bold text-amber-600">{{ $summary['expiring_soon'] }}</div><div class="text-xs text-gray-500">Expiring Soon</div></div>
        </div>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-100 text-left"><th class="p-2">Batch</th><th class="p-2">Expiry</th><th class="p-2">Qty</th><th class="p-2">Status</th></tr></thead>
            <tbody>
            @foreach($summary['batches'] as $b)
            <tr class="border-b">
                <td class="p-2 font-mono">{{ $b->batch_number }}</td>
                <td class="p-2">{{ $b->expiry_date->format('M Y') }}</td>
                <td class="p-2 font-bold">{{ $b->quantity }}</td>
                <td class="p-2">
                    @if($b->quantity <= 0)<span class="text-red-600 text-xs">Out of Stock</span>
                    @elseif($b->isExpired())<span class="text-red-600 text-xs">Expired</span>
                    @elseif($b->isExpiringSoon())<span class="text-amber-600 text-xs">Expiring Soon</span>
                    @else<span class="text-green-600 text-xs">OK</span>@endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
