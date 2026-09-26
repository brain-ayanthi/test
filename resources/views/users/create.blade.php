@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="space-y-3">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Email *</label><input type="email" name="email" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Phone</label><input type="text" name="phone" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Password *</label><input type="password" name="password" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Confirm Password *</label><input type="password" name="password_confirmation" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Role *</label>
                <select name="role" required class="w-full border rounded-lg p-2">
                    @foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <button class="mt-4 bg-green-500 text-white px-6 py-2 rounded-lg font-semibold">Create User</button>
    </form>
</div>
@endsection
