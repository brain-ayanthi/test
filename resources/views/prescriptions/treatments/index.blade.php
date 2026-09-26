@extends('layouts.app')
@section('title', 'Ready Treatments')
@section('page-title', 'Ready Treatment Templates')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex justify-between mb-4">
        <h3 class="font-bold">Ready Treatment Templates</h3>
        <a href="{{ route('treatments.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-plus mr-1"></i> New Template</a>
    </div>
    <p class="text-sm text-gray-500 mb-4">Templates auto-fill medicines in the prescription builder for recurring diseases (e.g. common fever).</p>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Name</th><th class="p-2">Disease</th><th class="p-2">Doctor</th>
            <th class="p-2">Medicines</th><th class="p-2"></th>
        </tr></thead>
        <tbody>
        @forelse($treatments as $t)
        <tr class="border-b">
            <td class="p-2 font-semibold">{{ $t->name }}</td>
            <td class="p-2 text-gray-500">{{ $t->disease }}</td>
            <td class="p-2">{{ $t->doctor?->name ?? '-' }}</td>
            <td class="p-2"><span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold">{{ $t->items_count }}</span></td>
            <td class="p-2">
                <a href="{{ route('treatments.edit', $t) }}" class="text-yellow-500"><i class="fas fa-edit"></i></a>
                <form method="POST" action="{{ route('treatments.destroy', $t) }}" class="inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 ml-2"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="p-6 text-center text-gray-400">No templates yet. <a href="{{ route('treatments.create') }}" class="text-blue-600 underline">Create one</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $treatments->links() }}</div>
</div>
@endsection
