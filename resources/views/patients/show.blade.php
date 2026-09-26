@extends('layouts.app')
@section('title', $patient->name)
@section('page-title', 'Patient Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-center mb-4">
            <div class="w-24 h-24 mx-auto bg-gray-200 rounded-full flex items-center justify-center text-gray-400">
                <i class="fas fa-user text-5xl"></i>
            </div>
            <h2 class="text-xl font-bold mt-2">{{ $patient->name }}</h2>
            <p class="text-gray-500 text-sm font-mono">{{ $patient->patient_code }}</p>
        </div>
        <div class="space-y-2 text-sm">
            <p><i class="fas fa-phone w-5 text-gray-400"></i> {{ $patient->phone ?? '-' }}</p>
            <p><i class="fas fa-envelope w-5 text-gray-400"></i> {{ $patient->email ?? '-' }}</p>
            <p><i class="fas fa-birthday-cake w-5 text-gray-400"></i> {{ $patient->age }} years ({{ $patient->gender }})</p>
            <p><i class="fas fa-map-marker w-5 text-gray-400"></i> {{ $patient->address ?? '-' }}</p>
            <p><i class="fas fa-tint w-5 text-gray-400"></i> Blood: {{ $patient->blood_group ?? '-' }}</p>
        </div>
        <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" class="mt-4 block text-center bg-green-500 text-white py-2 rounded-lg font-semibold">
            <i class="fas fa-plus mr-1"></i> New Prescription
        </a>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-lg mb-3">Prescription History</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2">Rx #</th>
                    <th class="p-2">Date</th>
                    <th class="p-2">Items</th>
                    <th class="p-2">Total</th>
                    <th class="p-2">Status</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient->prescriptions as $rx)
                <tr class="border-b">
                    <td class="p-2 text-blue-600 font-mono">{{ $rx->prescription_number }}</td>
                    <td class="p-2">{{ $rx->prescription_date->format('d M Y') }}</td>
                    <td class="p-2">{{ $rx->items->count() }}</td>
                    <td class="p-2 font-bold">Rs {{ number_format($rx->total_fee, 2) }}</td>
                    <td class="p-2"><span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">{{ ucfirst($rx->status) }}</span></td>
                    <td class="p-2"><a href="{{ route('prescriptions.show', $rx) }}" class="text-blue-500"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-4 text-center text-gray-400">No prescriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
