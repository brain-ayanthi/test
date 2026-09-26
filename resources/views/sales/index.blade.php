@extends('layouts.app')
@section('title', 'Invoices')
@section('page-title', 'Sales / Invoices')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex justify-between mb-4">
        <h3 class="font-bold">All Invoices</h3>
        <a href="{{ route('sales.pos') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-cash-register mr-1"></i> Open POS</a>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Invoice #</th><th class="p-2">Customer</th><th class="p-2">Date</th>
            <th class="p-2">Items</th><th class="p-2">Total</th><th class="p-2">Paid</th><th class="p-2">Status</th><th class="p-2"></th>
        </tr></thead>
        <tbody>
            @forelse($sales as $s)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 font-mono text-blue-600">{{ $s->invoice_number }}</td>
                <td class="p-2">{{ $s->customer_name ?? 'Walking Customer' }}</td>
                <td class="p-2">{{ $s->sale_date->format('d M Y') }}</td>
                <td class="p-2">{{ $s->items->count() }}</td>
                <td class="p-2 font-bold">Rs {{ number_format($s->total, 2) }}</td>
                <td class="p-2 text-green-600">Rs {{ number_format($s->paid_amount, 2) }}</td>
                <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold
                    {{ $s->payment_status==='paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($s->payment_status) }}</span></td>
                <td class="p-2">
                    <a href="{{ route('sales.show', $s) }}" class="text-blue-500"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('sales.print', $s) }}" target="_blank" class="text-green-500 ml-2"><i class="fas fa-print"></i></a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="p-6 text-center text-gray-400">No sales yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $sales->links() }}</div>
</div>
@endsection
