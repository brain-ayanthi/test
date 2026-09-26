@extends('layouts.app')
@section('title', 'Doctors')
@section('page-title', 'Doctor Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold mb-4">Doctors</h3>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Name</th><th class="p-2">Specialization</th><th class="p-2">Phone</th>
            <th class="p-2">Chamber</th><th class="p-2">Visit Fee</th><th class="p-2">Doctor Fee</th>
        </tr></thead>
        <tbody>
        @foreach($doctors as $d)
        <tr class="border-b">
            <td class="p-2 font-semibold">{{ $d->name }}</td>
            <td class="p-2">{{ $d->specialization }}</td>
            <td class="p-2">{{ $d->phone }}</td>
            <td class="p-2">{{ $d->chamber }}</td>
            <td class="p-2">Rs {{ number_format($d->visit_fee,2) }}</td>
            <td class="p-2 font-bold">Rs {{ number_format($d->doctor_fee,2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $doctors->links() }}</div>
</div>
@endsection
