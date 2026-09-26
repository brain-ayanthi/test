@extends('layouts.app')
@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-4xl">
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" value="{{ $product->name }}" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">SKU *</label><input type="text" name="sku" value="{{ $product->sku }}" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Form Type</label><input type="text" name="form_type" value="{{ $product->form_type }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Strength</label><input type="text" name="strength" value="{{ $product->strength }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Drug Type</label>
                <select name="drug_type_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($drugTypes as $d)<option value="{{ $d->id }}" @selected($product->drug_type_id==$d->id)>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-sm font-semibold mb-1">Selling Price</label><input type="number" step="0.01" name="selling_price" value="{{ $product->selling_price }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Purchase Price</label><input type="number" step="0.01" name="purchase_price" value="{{ $product->purchase_price }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Rack</label><input type="text" name="rack_number" value="{{ $product->rack_number }}" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Min Stock</label><input type="number" name="min_stock" value="{{ $product->min_stock }}" class="w-full border rounded-lg p-2"></div>
        </div>
        <button class="mt-4 bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">Update Product</button>
    </form>
</div>
@endsection
