@extends('layouts.app')
@section('title', 'Low Stock')
@section('page-title', 'Low Stock Alert')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Product</th><th class="p-2">SKU</th><th class="p-2">Current Stock</th><th class="p-2">Min Stock</th>
        </tr></thead>
        <tbody>
        @forelse($data as $r)
        <tr class="border-b">
            <td class="p-2 font-semibold">{{ $r->name }}</td>
            <td class="p-2 font-mono text-gray-500">{{ $r->sku }}</td>
            <td class="p-2 font-bold text-red-600">{{ $r->stock }}</td>
            <td class="p-2">{{ $r->min_stock }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="p-6 text-center text-gray-400">All products well stocked.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
