@extends('layouts.app')
@section('title', 'Vendors')
@section('page-title', 'Vendors / Suppliers')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Add Vendor</h3>
        <form method="POST" action="{{ route('vendors.store') }}">
            @csrf
            <input type="text" name="name" required placeholder="Vendor name" class="w-full border rounded-lg p-2 mb-2">
            <input type="text" name="company" placeholder="Company" class="w-full border rounded-lg p-2 mb-2">
            <input type="text" name="phone" placeholder="Phone" class="w-full border rounded-lg p-2 mb-2">
            <input type="email" name="email" placeholder="Email" class="w-full border rounded-lg p-2 mb-2">
            <textarea name="address" placeholder="Address" class="w-full border rounded-lg p-2 mb-2" rows="2"></textarea>
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Add Vendor</button>
        </form>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-100 text-left"><th class="p-2">Name</th><th class="p-2">Company</th><th class="p-2">Phone</th><th class="p-2">Purchases</th></tr></thead>
            <tbody>
            @foreach($vendors as $v)
            <tr class="border-b"><td class="p-2 font-semibold">{{ $v->name }}</td><td class="p-2 text-gray-500">{{ $v->company }}</td><td class="p-2">{{ $v->phone }}</td><td class="p-2">{{ $v->purchases->count() }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
