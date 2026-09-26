@extends('layouts.app')
@section('title', 'Doctor Wise Commission')
@section('page-title', 'Doctor Wise Commission Report')

@section('content')
<form method="GET" class="mb-4 flex gap-2 items-end">
    <div><label class="block text-xs font-semibold text-gray-600">From</label><input type="date" name="from" value="{{ $from }}" class="border rounded p-2"></div>
    <div><label class="block text-xs font-semibold text-gray-600">To</label><input type="date" name="to" value="{{ $to }}" class="border rounded p-2"></div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Apply</button>
    <a href="{{ route('reports.index') }}" class="text-blue-600 text-sm self-center">Back to Dashboard</a>
</form>

<div class="bg-white rounded-xl shadow p-5">
    <h3 class="font-bold text-lg mb-4">Doctor Fees & Patient Counts ({{ $from }} to {{ $to }})</h3>
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Doctor</th>
                <th class="p-2">Prescriptions</th>
                <th class="p-2">Patients</th>
                <th class="p-2 text-right">Medicine</th>
                <th class="p-2 text-right">Radiology</th>
                <th class="p-2 text-right bg-yellow-50">Doctor Fee</th>
                <th class="p-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rx->doctor_wise as $d)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 font-semibold">{{ $d->doctor }}</td>
                <td class="p-2 text-center">{{ $d->prescriptions }}</td>
                <td class="p-2 text-center">{{ $d->patients }}</td>
                <td class="p-2 text-right">Rs {{ number_format($d->medicine,2) }}</td>
                <td class="p-2 text-right">Rs {{ number_format($d->radiology,2) }}</td>
                <td class="p-2 text-right font-bold text-rose-600 bg-yellow-50">Rs {{ number_format($d->doctor_fee,2) }}</td>
                <td class="p-2 text-right font-bold">Rs {{ number_format($d->total,2) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-center text-gray-400">No data in selected period.</td></tr>
        @endforelse
        </tbody>
        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td class="p-2">TOTAL</td>
                <td class="p-2 text-center">{{ $rx->rx_count }}</td>
                <td></td>
                <td class="p-2 text-right">Rs {{ number_format($rx->medicine,2) }}</td>
                <td class="p-2 text-right">Rs {{ number_format($rx->radiology,2) }}</td>
                <td class="p-2 text-right text-rose-600">Rs {{ number_format($rx->doctor_fee,2) }}</td>
                <td class="p-2 text-right">Rs {{ number_format($rx->total,2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
