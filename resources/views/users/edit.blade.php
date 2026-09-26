@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="space-y-3">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" value="{{ $user->name }}" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Email *</label><input type="email" name="email" value="{{ $user->email }}" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Phone</label><input type="text" name="phone" value="{{ $user->phone }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">New Password (leave blank to keep)</label><input type="password" name="password" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Confirm Password</label><input type="password" name="password_confirmation" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Role *</label>
                <select name="role" required class="w-full border rounded-lg p-2">
                    @foreach($roles as $r)<option value="{{ $r->name }}" @selected($user->hasRole($r->name))>{{ $r->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <button class="mt-4 bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">Update User</button>
    </form>
</div>
@endsection
