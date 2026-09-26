@extends('layouts.app')
@section('title', 'Drug Types')
@section('page-title', 'Drug Types (Quick Pick)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Add Drug Type</h3>
        <form method="POST" action="{{ route('drug-types.store') }}">
            @csrf
            <input type="text" name="name" required placeholder="e.g. Antibiotic" class="w-full border rounded-lg p-2 mb-2">
            <input type="color" name="color" value="#22c55e" class="w-full border rounded-lg p-1 mb-2 h-10">
            <textarea name="description" placeholder="Description" class="w-full border rounded-lg p-2 mb-2" rows="2"></textarea>
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Add Drug Type</button>
        </form>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($drugTypes as $d)
            <div class="rounded-lg p-4 text-white font-semibold" style="background:{{ $d->color }}">
                <div>{{ $d->name }}</div>
                <div class="text-xs opacity-80">{{ $d->products->count() }} medicines</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
