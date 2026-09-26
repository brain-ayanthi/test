@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User & Role Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex justify-between mb-4">
        <h3 class="font-bold">System Users</h3>
        <a href="{{ route('users.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-plus mr-1"></i> Add User</a>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Name</th><th class="p-2">Email</th><th class="p-2">Phone</th>
            <th class="p-2">Role</th><th class="p-2">Status</th><th class="p-2"></th>
        </tr></thead>
        <tbody>
        @foreach($users as $u)
        <tr class="border-b hover:bg-gray-50">
            <td class="p-2 font-semibold">{{ $u->name }}</td>
            <td class="p-2">{{ $u->email }}</td>
            <td class="p-2">{{ $u->phone }}</td>
            <td class="p-2">
                @foreach($u->roles as $r)<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-semibold">{{ $r->name }}</span>@endforeach
            </td>
            <td class="p-2">
                <span class="px-2 py-0.5 rounded text-xs {{ $u->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td class="p-2">
                <a href="{{ route('users.edit', $u) }}" class="text-yellow-500"><i class="fas fa-edit"></i></a>
                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Delete user?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 ml-2"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
