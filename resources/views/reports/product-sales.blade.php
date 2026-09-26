@extends('layouts.app')
@section('title', 'Product Sales')
@section('page-title', 'Product-wise Sales')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left"><th class="p-2">Product</th><th class="p-2">SKU</th><th class="p-2">Qty Sold</th><th class="p-2">Revenue</th></tr></thead>
        <tbody>
        @forelse($data as $r)
        <tr class="border-b"><td class="p-2 font-semibold">{{ $r->name }}</td><td class="p-2 font-mono text-gray-500">{{ $r->sku }}</td><td class="p-2">{{ $r->qty }}</td><td class="p-2 font-bold text-green-600">Rs {{ number_format($r->total,2) }}</td></tr>
        @empty
        <tr><td colspan="4" class="p-6 text-center text-gray-400">No sales data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
