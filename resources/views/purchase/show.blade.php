@extends('layouts.app')
@section('title', $purchase->invoice_number)
@section('page-title', 'Purchase Invoice')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between mb-6 border-b pb-4">
        <div>
            <h2 class="text-2xl font-bold">{{ $purchase->invoice_number }}</h2>
            <p class="text-gray-500">Vendor: <strong>{{ $purchase->vendor->name }}</strong></p>
            <p class="text-gray-500">Date: {{ $purchase->purchase_date->format('d M Y') }}</p>
        </div>
        <div class="text-right">
            <span class="px-3 py-1 rounded-lg text-sm font-bold
                {{ $purchase->payment_status==='paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ strtoupper($purchase->payment_status) }}
            </span>
        </div>
    </div>

    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Product</th><th class="p-2">Batch</th><th class="p-2">Expiry</th>
            <th class="p-2">Qty (boxes)</th><th class="p-2">Total Pcs</th>
            <th class="p-2">Price/Box</th><th class="p-2">Cost/Pc</th><th class="p-2">Total</th>
        </tr></thead>
        <tbody>
        @foreach($purchase->items as $item)
        <tr class="border-b">
            <td class="p-2 font-semibold">{{ $item->product->name }}</td>
            <td class="p-2">{{ $item->batch_number }}</td>
            <td class="p-2">{{ $item->expiry_date?->format('M Y') }}</td>
            <td class="p-2">{{ $item->purchase_quantity }} {{ $item->purchaseUnit?->short_name }}</td>
            <td class="p-2 font-bold text-blue-600">{{ $item->total_pieces }}</td>
            <td class="p-2">Rs {{ number_format($item->purchase_price, 2) }}</td>
            <td class="p-2 text-gray-500">Rs {{ number_format($item->unit_cost, 4) }}</td>
            <td class="p-2 font-bold">Rs {{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <div class="flex justify-end">
        <div class="w-72 space-y-1 text-sm">
            <div class="flex justify-between"><span>Subtotal:</span><span>Rs {{ number_format($purchase->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span>Discount:</span><span>- Rs {{ number_format($purchase->discount, 2) }}</span></div>
            <div class="flex justify-between"><span>Tax:</span><span>Rs {{ number_format($purchase->tax, 2) }}</span></div>
            <div class="flex justify-between"><span>Shipping:</span><span>Rs {{ number_format($purchase->shipping, 2) }}</span></div>
            <div class="flex justify-between text-lg font-bold border-t pt-1"><span>Total:</span><span>Rs {{ number_format($purchase->total, 2) }}</span></div>
            <div class="flex justify-between text-green-600"><span>Paid:</span><span>Rs {{ number_format($purchase->paid_amount, 2) }}</span></div>
            <div class="flex justify-between text-red-600 font-bold"><span>Due:</span><span>Rs {{ number_format($purchase->due_amount, 2) }}</span></div>
        </div>
    </div>
</div>
@endsection
