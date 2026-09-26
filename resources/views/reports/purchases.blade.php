@extends('layouts.app')
@section('title', 'Purchase Report')
@section('page-title', 'Purchase Report')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left"><th class="p-2">Invoice #</th><th class="p-2">Vendor</th><th class="p-2">Date</th><th class="p-2">Total</th><th class="p-2">Paid</th><th class="p-2">Due</th><th class="p-2">Status</th></tr></thead>
        <tbody>
        @forelse($data as $p)
        <tr class="border-b">
            <td class="p-2 font-mono text-blue-600">{{ $p->invoice_number }}</td>
            <td class="p-2">{{ $p->vendor->name }}</td>
            <td class="p-2">{{ $p->purchase_date->format('d M Y') }}</td>
            <td class="p-2 font-bold">Rs {{ number_format($p->total,2) }}</td>
            <td class="p-2 text-green-600">Rs {{ number_format($p->paid_amount,2) }}</td>
            <td class="p-2 text-red-600">Rs {{ number_format($p->due_amount,2) }}</td>
            <td class="p-2 capitalize">{{ $p->payment_status }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="p-6 text-center text-gray-400">No purchases.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
