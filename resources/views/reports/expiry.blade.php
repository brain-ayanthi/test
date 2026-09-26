@extends('layouts.app')
@section('title', 'Expiry Report')
@section('page-title', 'Expiry Report')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Product</th><th class="p-2">Batch</th><th class="p-2">Expiry</th>
            <th class="p-2">Quantity</th><th class="p-2">Status</th>
        </tr></thead>
        <tbody>
        @forelse($data as $r)
        <tr class="border-b">
            <td class="p-2 font-semibold">{{ $r['product'] }}</td>
            <td class="p-2 font-mono">{{ $r['batch'] }}</td>
            <td class="p-2">{{ $r['expiry'] }}</td>
            <td class="p-2">{{ $r['quantity'] }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $r['status']==='Expired' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $r['status'] }}</span></td>
        </tr>
        @empty
        <tr><td colspan="5" class="p-6 text-center text-gray-400">No expiring products.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
