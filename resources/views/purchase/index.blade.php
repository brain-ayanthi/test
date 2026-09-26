@extends('layouts.app')
@section('title', 'Purchases')
@section('page-title', 'Purchase Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex justify-between mb-4">
        <h3 class="font-bold">Purchase Invoices</h3>
<div class="flex gap-2">
            <a href="{{ route('vendor-payments.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-money-check-alt mr-1"></i> Vendor Payments</a>
            <a href="{{ route('purchases.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-plus mr-1"></i> New Purchase</a>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Invoice #</th><th class="p-2">Vendor</th><th class="p-2">Date</th>
            <th class="p-2">Total</th><th class="p-2">Paid</th><th class="p-2">Due</th><th class="p-2">Status</th><th class="p-2"></th>
        </tr></thead>
        <tbody>
            @forelse($purchases as $p)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 font-mono text-blue-600">{{ $p->invoice_number }}</td>
                <td class="p-2">{{ $p->vendor->name }}</td>
                <td class="p-2">{{ $p->purchase_date->format('d M Y') }}</td>
                <td class="p-2 font-bold">Rs {{ number_format($p->total, 2) }}</td>
                <td class="p-2 text-green-600">Rs {{ number_format($p->paid_amount, 2) }}</td>
                <td class="p-2 text-red-600">Rs {{ number_format($p->due_amount, 2) }}</td>
                <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold
                    {{ $p->payment_status==='paid' ? 'bg-green-100 text-green-700' : ($p->payment_status==='partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($p->payment_status) }}</span></td>
                <td class="p-2"><a href="{{ route('purchases.show', $p) }}" class="text-blue-500"><i class="fas fa-eye"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="p-6 text-center text-gray-400">No purchases yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $purchases->links() }}</div>
</div>
@endsection
