@extends('layouts.app')
@section('title', 'Vendor Payments')
@section('page-title', 'Vendor Outstanding & Payments')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <div class="text-sm text-gray-500">Total Outstanding</div>
        <div class="text-3xl font-bold text-red-600 mt-1">Rs {{ number_format($vendors->sum('outstanding'), 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <div class="text-sm text-gray-500">Total Purchases</div>
        <div class="text-3xl font-bold text-blue-600 mt-1">Rs {{ number_format($vendors->sum('total_purchased'), 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <div class="text-sm text-gray-500">Total Paid</div>
        <div class="text-3xl font-bold text-green-600 mt-1">Rs {{ number_format($vendors->sum('total_paid'), 2) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <h3 class="font-bold text-lg mb-3">Vendors with Outstanding Balance</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2">Vendor</th>
                <th class="p-2">Purchases</th>
                <th class="p-2">Total Billed</th>
                <th class="p-2">Paid</th>
                <th class="p-2">Outstanding</th>
                <th class="p-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $v)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2">
                    <a href="{{ route('vendor-payments.show', $v->id) }}" class="font-semibold text-blue-600 hover:underline">{{ $v->name }}</a>
                    @if($v->company)<div class="text-xs text-gray-500">{{ $v->company }}</div>@endif
                </td>
                <td class="p-2">{{ $v->purchases_count }}</td>
                <td class="p-2">Rs {{ number_format($v->total_purchased ?? 0, 2) }}</td>
                <td class="p-2 text-green-600">Rs {{ number_format($v->total_paid ?? 0, 2) }}</td>
                <td class="p-2 font-bold {{ $v->outstanding > 0 ? 'text-red-600' : 'text-green-600' }}">Rs {{ number_format($v->outstanding, 2) }}</td>
                <td class="p-2">
                    <a href="{{ route('vendor-payments.show', $v->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold">
                        <i class="fas fa-money-bill mr-1"></i> Pay
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-6 text-center text-gray-400">No vendors found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold text-lg mb-3">Recent Payments</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2">Date</th>
                <th class="p-2">Vendor</th>
                <th class="p-2">Invoice</th>
                <th class="p-2">Method</th>
                <th class="p-2 text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPayments as $p)
            <tr class="border-b">
                <td class="p-2">{{ $p->payment_date->format('d M Y') }}</td>
                <td class="p-2 font-semibold">{{ $p->vendor->name ?? '-' }}</td>
                <td class="p-2">{{ $p->purchase->invoice_number ?? 'General Payment' }}</td>
                <td class="p-2 uppercase text-xs">{{ $p->payment_method }}</td>
                <td class="p-2 text-right font-bold text-green-600">Rs {{ number_format($p->amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-6 text-center text-gray-400">No payments recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
