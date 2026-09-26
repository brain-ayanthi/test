@extends('layouts.app')
@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="date" name="from" value="{{ $from }}" class="border rounded-lg p-2">
        <input type="date" name="to" value="{{ $to }}" class="border rounded-lg p-2">
        <select name="group_by" class="border rounded-lg p-2">
            <option value="day" @selected($group==='day')>Daily</option>
            <option value="week" @selected($group==='week')>Weekly</option>
            <option value="month" @selected($group==='month')>Monthly</option>
        </select>
        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">Generate</button>
    </form>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Period</th><th class="p-2">Invoices</th><th class="p-2">Subtotal</th>
            <th class="p-2">Discount</th><th class="p-2">Total</th><th class="p-2">Paid</th><th class="p-2">Due</th>
        </tr></thead>
        <tbody>
        @forelse($data as $row)
        <tr class="border-b">
            <td class="p-2 font-mono">{{ $row->period }}</td>
            <td class="p-2">{{ $row->invoices }}</td>
            <td class="p-2">Rs {{ number_format($row->subtotal,2) }}</td>
            <td class="p-2 text-red-600">Rs {{ number_format($row->discount,2) }}</td>
            <td class="p-2 font-bold">Rs {{ number_format($row->total,2) }}</td>
            <td class="p-2 text-green-600">Rs {{ number_format($row->paid,2) }}</td>
            <td class="p-2 text-red-600">Rs {{ number_format($row->due,2) }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="p-6 text-center text-gray-400">No data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
