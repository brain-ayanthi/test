@extends('layouts.app')
@section('title', 'Edit Patient')
@section('page-title', 'Edit Patient')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form method="POST" action="{{ route('patients.update', $patient) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" value="{{ $patient->name }}" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Email</label><input type="email" name="email" value="{{ $patient->email }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Phone</label><input type="text" name="phone" value="{{ $patient->phone }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Age</label><input type="number" name="age" value="{{ $patient->age }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Gender</label>
                <select name="gender" class="w-full border rounded-lg p-2">
                    @foreach(['male','female','other'] as $g)
                    <option value="{{ $g }}" @selected($patient->gender===$g)>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-semibold mb-1">Blood Group</label><input type="text" name="blood_group" value="{{ $patient->blood_group }}" class="w-full border rounded-lg p-2"></div>
            <div class="col-span-2"><label class="block text-sm font-semibold mb-1">Address</label><textarea name="address" class="w-full border rounded-lg p-2" rows="2">{{ $patient->address }}</textarea></div>
        </div>
        <button class="mt-4 bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">Update Patient</button>
    </form>
</div>
@endsection
