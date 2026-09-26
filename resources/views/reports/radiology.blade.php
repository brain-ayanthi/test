@extends('layouts.app')
@section('title', 'Radiology Report')
@section('page-title', 'Radiology / Extra Tests Report')

@section('content')
<form method="GET" class="mb-4 flex gap-2 items-end">
    <div><label class="block text-xs font-semibold text-gray-600">From</label><input type="date" name="from" value="{{ $from }}" class="border rounded p-2"></div>
    <div><label class="block text-xs font-semibold text-gray-600">To</label><input type="date" name="to" value="{{ $to }}" class="border rounded p-2"></div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Apply</button>
    <a href="{{ route('reports.index') }}" class="text-blue-600 text-sm self-center">Back</a>
</form>

<div class="bg-white rounded-xl shadow p-5">
    <h3 class="font-bold text-lg mb-4">Radiology / Extra Tests Revenue</h3>
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Test Name</th>
                <th class="p-2 text-center">Times Performed</th>
                <th class="p-2 text-right">Revenue</th>
                <th class="p-2 w-40">Share</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rx->test_wise as $t)
            @php $pct = $rx->radiology > 0 ? ($t->total / $rx->radiology * 100) : 0; @endphp
            <tr class="border-b">
                <td class="p-2 font-semibold">{{ $t->test_name }}</td>
                <td class="p-2 text-center">{{ $t->count }}</td>
                <td class="p-2 text-right font-bold text-amber-600">Rs {{ number_format($t->total,2) }}</td>
                <td class="p-2">
                    <div class="bg-gray-200 rounded h-2"><div class="bg-amber-500 h-2 rounded" style="width:{{ $pct }}%"></div></div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="p-6 text-center text-gray-400">No tests in this period.</td></tr>
        @endforelse
        </tbody>
        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td class="p-2">TOTAL</td>
                <td class="p-2 text-center">{{ $rx->test_wise->sum('count') }}</td>
                <td class="p-2 text-right text-amber-600">Rs {{ number_format($rx->radiology,2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
