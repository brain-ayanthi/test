@extends('layouts.app')
@section('title', 'Prescriptions')
@section('page-title', 'Prescription History')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex justify-between mb-4">
        <h3 class="font-bold">All Prescriptions</h3>
        <a href="{{ route('prescriptions.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-plus mr-1"></i> New Prescription</a>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Rx #</th><th class="p-2">Patient</th><th class="p-2">Doctor</th>
            <th class="p-2">Date</th><th class="p-2">Medicine Cost</th><th class="p-2">Total</th>
            <th class="p-2">Status</th><th class="p-2">Sale</th><th class="p-2"></th>
        </tr></thead>
        <tbody>
        @forelse($prescriptions as $rx)
        <tr class="border-b hover:bg-gray-50">
            <td class="p-2 font-mono text-blue-600">{{ $rx->prescription_number }}</td>
            <td class="p-2">{{ $rx->patient_name }}</td>
            <td class="p-2">{{ $rx->doctor?->name ?? '-' }}</td>
            <td class="p-2">{{ $rx->prescription_date->format('d M Y') }}</td>
            <td class="p-2">Rs {{ number_format($rx->medicine_cost,2) }}</td>
            <td class="p-2 font-bold">Rs {{ number_format($rx->total_fee,2) }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs
                {{ $rx->status==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($rx->status) }}</span></td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs
                {{ $rx->sale_status==='sold' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($rx->sale_status) }}</span></td>
            <td class="p-2">
                <a href="{{ route('prescriptions.show', $rx) }}" class="text-blue-500" title="View"><i class="fas fa-eye"></i></a>
                <button type="button"
                        onclick="ClinicMSPrintServer.printExisting({{ $rx->id }}, @js($rx->prescription_number), this)"
                        class="text-green-600 ml-2 disabled:opacity-50"
                        title="Direct Print via ClinicMS Print Server">
                    <i class="fas fa-print"></i>
                </button>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" class="p-6 text-center text-gray-400">No prescriptions yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $prescriptions->links() }}</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/clinicms-print-server.js') }}"></script>
@endpush
