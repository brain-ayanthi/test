@extends('layouts.app')
@section('title', 'Patients')
@section('page-title', 'Patient Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex flex-col md:flex-row justify-between gap-3 mb-4">
        <form method="GET" class="flex gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, code..."
                   class="flex-1 border rounded-lg px-4 py-2">
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('prescriptions.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold">
            <i class="fas fa-plus mr-1"></i> New Prescription
        </a>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 text-left text-gray-700">
                <th class="p-3">Patient ID</th>
                <th class="p-3">Name</th>
                <th class="p-3">Phone</th>
                <th class="p-3">Age</th>
                <th class="p-3">Gender</th>
                <th class="p-3">Last Visit</th>
                <th class="p-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $p)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3 font-mono text-blue-600">{{ $p->patient_code }}</td>
                <td class="p-3 font-semibold">{{ $p->name }}</td>
                <td class="p-3">{{ $p->phone }}</td>
                <td class="p-3">{{ $p->age }}</td>
                <td class="p-3 capitalize">{{ $p->gender }}</td>
                <td class="p-3">{{ $p->last_visit?->format('d M Y') ?? '-' }}</td>
                <td class="p-3 space-x-2">
                    <a href="{{ route('patients.show', $p) }}" class="text-blue-500"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('prescriptions.create', ['patient_id' => $p->id]) }}" class="text-green-500" title="New Rx">
                        <i class="fas fa-prescription"></i>
                    </a>
                    <a href="{{ route('patients.edit', $p) }}" class="text-yellow-500"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{ route('patients.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="text-red-500"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-6 text-center text-gray-400">No patients found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $patients->links() }}</div>
</div>
@endsection
