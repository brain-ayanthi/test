@extends('layouts.app')
@section('title', 'Cash Store')
@section('page-title', 'Cash / Store Management')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    @foreach($accounts as $a)
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4" style="border-color: {{ $a->color ?? '#22c55e' }}">
        <div class="flex justify-between">
            <div>
                <p class="text-gray-500 text-sm">{{ $a->name }}</p>
                <p class="text-2xl font-bold mt-1">Rs {{ number_format($a->balance, 2) }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" style="background: {{ $a->color ?? '#22c55e' }}">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Top-up Account</h3>
        <form method="POST" action="{{ route('cash.topup') }}">
            @csrf
            <select name="account_id" class="w-full border rounded-lg p-2 mb-2" required>
                @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
            <input type="number" step="0.01" name="amount" placeholder="Amount" required class="w-full border rounded-lg p-2 mb-2">
            <input type="text" name="note" placeholder="Note" class="w-full border rounded-lg p-2 mb-2">
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Top Up</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Transfer Between Accounts</h3>
        <form method="POST" action="{{ route('cash.transfer') }}">
            @csrf
            <select name="from_account_id" class="w-full border rounded-lg p-2 mb-2" required>
                <option value="">From Account</option>
                @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
            <select name="to_account_id" class="w-full border rounded-lg p-2 mb-2" required>
                <option value="">To Account</option>
                @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
            <input type="number" step="0.01" name="amount" placeholder="Amount" required class="w-full border rounded-lg p-2 mb-2">
            <button class="w-full bg-blue-500 text-white py-2 rounded-lg font-semibold">Transfer</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">New Account</h3>
        <form method="POST" action="{{ route('cash.accounts.store') }}">
            @csrf
            <input type="text" name="name" placeholder="Account name" required class="w-full border rounded-lg p-2 mb-2">
            <select name="type" class="w-full border rounded-lg p-2 mb-2"><option value="cash">Cash</option><option value="bank">Bank</option><option value="mfs">MFS</option></select>
            <input type="number" step="0.01" name="opening_balance" placeholder="Opening balance" value="0" class="w-full border rounded-lg p-2 mb-2">
            <button class="w-full bg-gray-700 text-white py-2 rounded-lg font-semibold">Add Account</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 mt-5">
    <h3 class="font-bold mb-3">Transaction History</h3>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left"><th class="p-2">Date</th><th class="p-2">Account</th><th class="p-2">Type</th><th class="p-2">Amount</th><th class="p-2">Note</th></tr></thead>
        <tbody>
        @foreach($transactions as $t)
        <tr class="border-b">
            <td class="p-2">{{ $t->transaction_date->format('d M Y') }}</td>
            <td class="p-2">{{ $t->account->name }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs
                {{ $t->type==='income' ? 'bg-green-100 text-green-700' : ($t->type==='expense' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">{{ ucfirst($t->type) }}</span></td>
            <td class="p-2 font-bold {{ in_array($t->type,['income','transfer_in']) ? 'text-green-600' : 'text-red-600' }}">Rs {{ number_format($t->amount,2) }}</td>
            <td class="p-2 text-gray-500">{{ $t->note }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
