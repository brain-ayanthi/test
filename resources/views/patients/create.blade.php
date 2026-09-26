@extends('layouts.app')
@section('title', 'New Patient')
@section('page-title', 'Create Patient')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form method="POST" action="{{ route('patients.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Email</label><input type="email" name="email" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Phone</label><input type="text" name="phone" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Age</label><input type="number" name="age" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Gender</label>
                <select name="gender" class="w-full border rounded-lg p-2"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
            </div>
            <div><label class="block text-sm font-semibold mb-1">Blood Group</label><input type="text" name="blood_group" class="w-full border rounded-lg p-2"></div>
            <div class="col-span-2"><label class="block text-sm font-semibold mb-1">Address</label><textarea name="address" class="w-full border rounded-lg p-2" rows="2"></textarea></div>
            <div class="col-span-2"><label class="block text-sm font-semibold mb-1">Medical History</label><textarea name="medical_history" class="w-full border rounded-lg p-2" rows="2"></textarea></div>
            <div class="col-span-2"><label class="block text-sm font-semibold mb-1">Allergies</label><textarea name="allergies" class="w-full border rounded-lg p-2" rows="2"></textarea></div>
        </div>
        <button class="mt-4 bg-green-500 text-white px-6 py-2 rounded-lg font-semibold">Save Patient</button>
    </form>
</div>
@endsection
