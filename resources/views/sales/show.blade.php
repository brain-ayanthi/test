@extends('layouts.app')
@section('title', $sale->invoice_number)
@section('page-title', 'Invoice')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    <div class="flex justify-between border-b pb-4 mb-4">
        <div>
            <h2 class="text-2xl font-bold">{{ $sale->invoice_number }}</h2>
            <p class="text-gray-500">Date: {{ $sale->sale_date->format('d M Y') }}</p>
            @if($sale->patient)<p>Patient: <strong>{{ $sale->patient->name }}</strong></p>@endif
            @if($sale->prescription)<p>Rx: <span class="font-mono text-blue-600">{{ $sale->prescription->prescription_number }}</span></p>@endif
        </div>
        <a href="{{ route('sales.print', $sale) }}" target="_blank" class="bg-green-500 text-white px-4 py-2 rounded-lg h-fit"><i class="fas fa-print mr-1"></i> Print</a>
    </div>
    <table class="w-full text-sm mb-4">
        <thead><tr class="bg-gray-100 text-left"><th class="p-2">Item</th><th class="p-2">Batch</th><th class="p-2">Qty</th><th class="p-2">Price</th><th class="p-2">Total</th></tr></thead>
        <tbody>
        @foreach($sale->items as $i)
        <tr class="border-b">
            <td class="p-2">{{ $i->product_name }}</td>
            <td class="p-2 text-gray-500">{{ $i->batch_number }}</td>
            <td class="p-2">{{ $i->quantity }}</td>
            <td class="p-2">Rs {{ number_format($i->unit_price,2) }}</td>
            <td class="p-2 font-bold">Rs {{ number_format($i->total,2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="flex justify-end">
        <div class="w-64 space-y-1 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span>Rs {{ number_format($sale->subtotal,2) }}</span></div>
            <div class="flex justify-between"><span>Doctor Fee</span><span>Rs {{ number_format($sale->doctor_fee,2) }}</span></div>
            <div class="flex justify-between"><span>Discount</span><span>- Rs {{ number_format($sale->discount,2) }}</span></div>
            <div class="flex justify-between text-lg font-bold border-t pt-1"><span>Total</span><span>Rs {{ number_format($sale->total,2) }}</span></div>
            <div class="flex justify-between text-green-600"><span>Paid</span><span>Rs {{ number_format($sale->paid_amount,2) }}</span></div>
            <div class="flex justify-between text-red-600 font-bold"><span>Due</span><span>Rs {{ number_format($sale->due_amount,2) }}</span></div>
        </div>
    </div>
</div>
@endsection
