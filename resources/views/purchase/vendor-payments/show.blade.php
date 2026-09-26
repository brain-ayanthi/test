@extends('layouts.app')
@section('title', $vendor->name.' - Payments')
@section('page-title', 'Vendor Payment History')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <div class="flex flex-wrap justify-between items-start gap-4">
        <div>
            <h2 class="text-2xl font-bold">{{ $vendor->name }}</h2>
            @if($vendor->company)<p class="text-gray-500">{{ $vendor->company }}</p>@endif
            <p class="text-sm text-gray-500 mt-1">
                @if($vendor->phone)<i class="fas fa-phone mr-1"></i> {{ $vendor->phone }}@endif
                @if($vendor->email)<span class="ml-3"><i class="fas fa-envelope mr-1"></i> {{ $vendor->email }}</span>@endif
            </p>
        </div>
        <a href="{{ route('vendor-payments.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Back to all vendors</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-xs text-blue-700 font-semibold">Total Billed</div>
            <div class="text-2xl font-bold text-blue-900">Rs {{ number_format($vendor->total_purchased ?? 0, 2) }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-xs text-green-700 font-semibold">Total Paid</div>
            <div class="text-2xl font-bold text-green-900">Rs {{ number_format($vendor->total_paid ?? 0, 2) }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-xs text-red-700 font-semibold">Outstanding</div>
            <div class="text-2xl font-bold text-red-700">Rs {{ number_format($vendor->outstanding, 2) }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-xs text-gray-700 font-semibold">Opening Balance</div>
            <div class="text-2xl font-bold text-gray-900">Rs {{ number_format($vendor->opening_balance, 2) }}</div>
        </div>
    </div>
</div>

{{-- Record Payment --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <h3 class="font-bold text-lg mb-3"><i class="fas fa-plus-circle text-green-600 mr-1"></i> Record Payment</h3>
    <form method="POST" action="{{ route('vendor-payments.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
        @csrf
        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Date *</label>
            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full border rounded p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Amount *</label>
            <input type="number" step="0.01" name="amount" min="0.01" value="{{ number_format($vendor->outstanding, 2, '.', '') }}" required class="w-full border rounded p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Method</label>
            <select name="payment_method" class="w-full border rounded p-2 text-sm">
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="mfs">MFS</option>
                <option value="cheque">Cheque</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Against Invoice</label>
            <select name="purchase_id" class="w-full border rounded p-2 text-sm">
                <option value="">-- General --</option>
                @foreach($purchases->where('due_amount', '>', 0) as $pc)
                <option value="{{ $pc->id }}">{{ $pc->invoice_number }} (Due Rs {{ number_format($pc->due_amount,2) }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Reference</label>
            <input type="text" name="reference" class="w-full border rounded p-2 text-sm" placeholder="Cheque no / ref">
        </div>
        <div>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full font-semibold text-sm">
                <i class="fas fa-check mr-1"></i> Save
            </button>
        </div>
    </form>
</div>

{{-- Unpaid purchases --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <h3 class="font-bold text-lg mb-3">Unpaid Invoices</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Invoice #</th>
                <th class="p-2">Date</th>
                <th class="p-2 text-right">Total</th>
                <th class="p-2 text-right">Paid</th>
                <th class="p-2 text-right">Due</th>
                <th class="p-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases->where('due_amount', '>', 0) as $pc)
            <tr class="border-b">
                <td class="p-2 font-semibold text-blue-600">{{ $pc->invoice_number }}</td>
                <td class="p-2">{{ $pc->purchase_date->format('d M Y') }}</td>
                <td class="p-2 text-right">Rs {{ number_format($pc->total,2) }}</td>
                <td class="p-2 text-right text-green-600">Rs {{ number_format($pc->paid_amount,2) }}</td>
                <td class="p-2 text-right font-bold text-red-600">Rs {{ number_format($pc->due_amount,2) }}</td>
                <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-800">{{ ucfirst($pc->payment_status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-4 text-center text-gray-400">No unpaid invoices.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Payment history --}}
<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold text-lg mb-3">Payment History</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Date</th>
                <th class="p-2 text-left">Invoice</th>
                <th class="p-2 text-left">Method</th>
                <th class="p-2 text-left">Reference</th>
                <th class="p-2 text-right">Amount</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $pm)
            <tr class="border-b">
                <td class="p-2">{{ $pm->payment_date->format('d M Y') }}</td>
                <td class="p-2">{{ $pm->purchase->invoice_number ?? 'General Payment' }}</td>
                <td class="p-2 uppercase text-xs">{{ $pm->payment_method }}</td>
                <td class="p-2 text-gray-500">{{ $pm->reference ?: '-' }}</td>
                <td class="p-2 text-right font-bold text-green-600">Rs {{ number_format($pm->amount,2) }}</td>
                <td class="p-2 text-right">
                    <form method="POST" action="{{ route('vendor-payments.destroy', $pm) }}" onsubmit="return confirm('Delete this payment?')" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-6 text-center text-gray-400">No payments recorded for this vendor.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
@endsection
