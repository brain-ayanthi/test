@extends('layouts.app')
@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
@if($errors->any())
<div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded">
    <ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-red-500 to-red-600 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">Today Expenses</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($todayTotal, 2) }}</div>
        <div class="text-xs opacity-80 mt-1">{{ now()->format('d M Y') }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">This Month</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($monthTotal, 2) }}</div>
        <div class="text-xs opacity-80 mt-1">{{ now()->format('F Y') }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">This Year</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($yearTotal, 2) }}</div>
        <div class="text-xs opacity-80 mt-1">{{ now()->format('Y') }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">Selected Period</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($selectedTotal, 2) }}</div>
        <div class="text-xs opacity-80 mt-1">{{ $from }} — {{ $to }}</div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas {{ $editExpense ? 'fa-edit text-blue-500' : 'fa-plus-circle text-red-500' }} mr-1"></i>
            {{ $editExpense ? 'Edit Expense' : 'Add Expense' }}
        </h3>
        <form method="POST" action="{{ $editExpense ? route('expenses.update', $editExpense) : route('expenses.store') }}" class="space-y-3">
            @csrf @if($editExpense) @method('PUT') @endif
            <div>
                <label class="block text-xs font-semibold mb-1">Date *</label>
                <input type="date" name="expense_date" required value="{{ old('expense_date', $editExpense?->expense_date?->format('Y-m-d') ?? now()->toDateString()) }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Expense Category *</label>
                <select name="expense_category_id" required class="w-full border rounded-lg p-2">
                    <option value="">Select category</option>
                    @foreach($activeCategories as $category)
                    <option value="{{ $category->id }}" @selected(old('expense_category_id', $editExpense?->expense_category_id)==$category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Title / Description *</label>
                <input type="text" name="title" required value="{{ old('title', $editExpense?->title) }}" placeholder="e.g. Electricity bill" class="w-full border rounded-lg p-2">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">Amount (Rs) *</label>
                    <input type="number" name="amount" required min="0.01" step="0.01" value="{{ old('amount', $editExpense?->amount) }}" class="w-full border rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Payment Method *</label>
                    <select name="payment_method" class="w-full border rounded-lg p-2">
                        @foreach(['cash'=>'Cash','bank'=>'Bank','mfs'=>'MFS','card'=>'Card','other'=>'Other'] as $value=>$label)
                        <option value="{{ $value }}" @selected(old('payment_method', $editExpense?->payment_method ?? 'cash')===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Reference</label>
                <input type="text" name="reference" value="{{ old('reference', $editExpense?->reference) }}" placeholder="Bill / receipt number" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded-lg p-2">{{ old('notes', $editExpense?->notes) }}</textarea>
            </div>
            <div class="flex gap-2">
                <button class="flex-1 {{ $editExpense ? 'bg-blue-600' : 'bg-red-600' }} text-white py-2.5 rounded-lg font-semibold">
                    {{ $editExpense ? 'Update Expense' : 'Save Expense' }}
                </button>
                @if($editExpense)<a href="{{ route('expenses.index') }}" class="border px-4 py-2.5 rounded-lg">Cancel</a>@endif
            </div>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-lg"><i class="fas fa-receipt text-red-500 mr-1"></i>Expenses</h3>
            <div class="flex gap-2">
                <a href="{{ route('expenses.report') }}" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-chart-bar mr-1"></i>Expense Report</a>
                <button type="button" onclick="document.getElementById('categoryPanel').classList.toggle('hidden')" class="bg-gray-800 text-white px-3 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-tags mr-1"></i>Categories</button>
            </div>
        </div>

        <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
            <div class="flex flex-wrap gap-1">
                @foreach(['today'=>'Today','yesterday'=>'Yesterday','7days'=>'7 Days','this_month'=>'Month','last_month'=>'Last Month','this_year'=>'Year'] as $value=>$label)
                <a href="{{ route('expenses.index', ['period'=>$value]) }}" class="px-3 py-2 rounded-lg border text-xs font-semibold {{ $period===$value ? 'bg-red-600 text-white' : 'bg-white' }}">{{ $label }}</a>
                @endforeach
            </div>
            <input type="date" name="from" value="{{ request('from') }}" class="border rounded-lg p-2 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border rounded-lg p-2 text-sm">
            <select name="category_id" class="border rounded-lg p-2 text-sm"><option value="">All Categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach</select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="border rounded-lg p-2 text-sm w-32">
            <button class="bg-blue-600 text-white px-3 py-2 rounded-lg"><i class="fas fa-filter"></i></button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-100 text-left"><th class="p-2">Date</th><th class="p-2">Category</th><th class="p-2">Title</th><th class="p-2">Payment</th><th class="p-2 text-right">Amount</th><th class="p-2"></th></tr></thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 whitespace-nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td class="p-2"><span class="px-2 py-1 rounded-full text-white text-xs" style="background:{{ $expense->category?->color }}">{{ $expense->category?->name }}</span></td>
                        <td class="p-2"><div class="font-semibold">{{ $expense->title }}</div><div class="text-xs text-gray-400">{{ $expense->reference }}</div></td>
                        <td class="p-2 uppercase text-xs">{{ $expense->payment_method }}</td>
                        <td class="p-2 text-right font-bold text-red-600">Rs {{ number_format($expense->amount, 2) }}</td>
                        <td class="p-2 whitespace-nowrap text-right">
                            <a href="{{ route('expenses.index', array_merge(request()->query(), ['edit'=>$expense->id])) }}" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')<button class="text-red-600"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    @empty<tr><td colspan="6" class="p-8 text-center text-gray-400">No expenses for this period.</td></tr>@endforelse
                </tbody>
                <tfoot><tr class="bg-red-50 font-bold"><td colspan="4" class="p-3">Selected Total</td><td class="p-3 text-right text-red-700">Rs {{ number_format($selectedTotal, 2) }}</td><td></td></tr></tfoot>
            </table>
        </div>
        <div class="mt-4">{{ $expenses->links() }}</div>
    </div>
</div>

<div id="categoryPanel" class="{{ request('show_categories') ? '' : 'hidden' }} bg-white rounded-xl shadow-sm p-5 mb-5">
    <div class="flex justify-between items-center mb-4"><h3 class="font-bold text-lg"><i class="fas fa-tags mr-1"></i>Expense Categories</h3><button onclick="this.closest('#categoryPanel').classList.add('hidden')" class="text-gray-500"><i class="fas fa-times"></i></button></div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <form method="POST" action="{{ route('expense-categories.store') }}" class="space-y-3 border rounded-lg p-4">@csrf
            <h4 class="font-semibold">Add Category</h4>
            <input type="text" name="name" required placeholder="Category name" class="w-full border rounded-lg p-2">
            <textarea name="description" rows="2" placeholder="Description" class="w-full border rounded-lg p-2"></textarea>
            <div><label class="text-xs font-semibold">Color</label><input type="color" name="color" value="#ef4444" class="w-full h-10 border rounded"></div>
            <button class="w-full bg-gray-800 text-white py-2 rounded-lg font-semibold">Add Category</button>
        </form>
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($categories as $category)
            <div class="border rounded-lg p-3">
                <form method="POST" action="{{ route('expense-categories.update', $category) }}">@csrf @method('PUT')
                    <div class="flex gap-2 items-center">
                        <input type="color" name="color" value="{{ $category->color }}" class="w-10 h-9 border rounded">
                        <input type="text" name="name" value="{{ $category->name }}" required class="flex-1 border rounded p-2 font-semibold">
                        <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label>
                    </div>
                    <input type="text" name="description" value="{{ $category->description }}" placeholder="Description" class="w-full border rounded p-2 text-sm mt-2">
                    <button class="text-blue-600 text-sm font-semibold mt-2">Update</button>
                </form>
                <form method="POST" action="{{ route('expense-categories.destroy', $category) }}" class="text-right -mt-5" onsubmit="return confirm('Delete category?')">@csrf @method('DELETE')<button class="text-red-600 text-sm">Delete</button></form>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold mb-4">Category Summary — {{ $from }} to {{ $to }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        @forelse($categorySummary as $row)
        <div class="border-l-4 rounded-lg bg-gray-50 p-3" style="border-color:{{ $row->color }}"><div class="text-sm font-semibold">{{ $row->name }}</div><div class="text-xl font-bold">Rs {{ number_format($row->total,2) }}</div><div class="text-xs text-gray-500">{{ $row->entries }} entries</div></div>
        @empty<p class="text-gray-400">No category data.</p>@endforelse
    </div>
</div>
@endsection
