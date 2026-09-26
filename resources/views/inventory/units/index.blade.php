@extends('layouts.app')
@section('title', 'Units')
@section('page-title', 'Measurement Units')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Add Unit</h3>
        <form method="POST" action="{{ route('units.store') }}">
            @csrf
            <input type="text" name="name" required placeholder="Unit name (e.g. Box)" class="w-full border rounded-lg p-2 mb-2">
            <input type="text" name="short_name" required placeholder="Short name (box)" class="w-full border rounded-lg p-2 mb-2">
            <input type="number" step="0.01" name="conversion" value="1" placeholder="Pieces per unit" class="w-full border rounded-lg p-2 mb-2">
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Add Unit</button>
        </form>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-100 text-left"><th class="p-2">Name</th><th class="p-2">Short</th><th class="p-2">Pieces/Unit</th><th class="p-2">Base</th></tr></thead>
            <tbody>
            @foreach($units as $u)
            <tr class="border-b"><td class="p-2 font-semibold">{{ $u->name }}</td><td class="p-2">{{ $u->short_name }}</td><td class="p-2">{{ $u->conversion }}</td><td class="p-2">{{ $u->is_base_unit ? 'Yes' : 'No' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
