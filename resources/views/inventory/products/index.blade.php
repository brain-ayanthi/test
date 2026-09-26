@extends('layouts.app')
@section('title', 'Inventory')
@section('page-title', 'Medicine / Product Inventory')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex flex-wrap gap-3 justify-between mb-4">
        <form method="GET" class="flex gap-2 flex-1 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search medicine..." class="border rounded-lg px-3 py-2 flex-1 min-w-[200px]">
            <select name="category_id" class="border rounded-lg px-3 py-2">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="drug_type_id" class="border rounded-lg px-3 py-2">
                <option value="">All Drug Types</option>
                @foreach($drugTypes as $d)
                <option value="{{ $d->id }}" @selected(request('drug_type_id')==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg"><i class="fas fa-search"></i></button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('categories.index') }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-tags"></i> Categories</a>
            <a href="{{ route('drug-types.index') }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-capsules"></i> Drug Types</a>
            <a href="{{ route('units.index') }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-balance-scale"></i> Units</a>
            @if(!$openingStockImported)
                <a href="{{ route('products.opening-stock.form') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-file-import mr-1"></i> Import Opening Stock (One Time)</a>
            @else
                <span class="bg-gray-200 text-gray-500 px-4 py-2 rounded-lg font-semibold cursor-not-allowed" title="Opening stock has already been imported">
                    <i class="fas fa-lock mr-1"></i> Opening Stock Imported
                </span>
            @endif
            <a href="{{ route('products.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-plus mr-1"></i> Add Product</a>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 text-left text-gray-700">
                <th class="p-2">SKU</th>
                <th class="p-2">Name</th>
                <th class="p-2">Form</th>
                <th class="p-2">Strength</th>
                <th class="p-2">Drug Type</th>
                <th class="p-2">Stock (pcs)</th>
                <th class="p-2">Purchase Unit</th>
                <th class="p-2">Sell Price</th>
                <th class="p-2">Rack</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $p)
            @php $stock = $p->stock_quantity; @endphp
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 font-mono text-xs text-gray-500">{{ $p->sku }}</td>
                <td class="p-2 font-semibold">{{ $p->name }}</td>
                <td class="p-2">{{ $p->form_type }}</td>
                <td class="p-2">{{ $p->strength }}</td>
                <td class="p-2">
                    @if($p->drugType)
                    <span class="px-2 py-0.5 rounded text-xs text-white" style="background:{{ $p->drugType->color }}">{{ $p->drugType->name }}</span>
                    @endif
                </td>
                <td class="p-2">
                    <span class="font-bold {{ $stock <= 0 ? 'text-red-600' : ($stock <= $p->min_stock ? 'text-amber-600' : 'text-green-600') }}">
                        {{ $stock }}
                    </span>
                </td>
                <td class="p-2 text-xs">{{ $p->pieces_per_purchase_unit }} pcs / {{ $p->purchaseUnit?->short_name }}</td>
                <td class="p-2 font-bold">Rs {{ number_format($p->selling_price, 2) }}</td>
                <td class="p-2 text-xs">{{ $p->rack_number }}</td>
                <td class="p-2">
                    <a href="{{ route('products.edit', $p) }}" class="text-yellow-500"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="p-6 text-center text-gray-400">No products.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection
