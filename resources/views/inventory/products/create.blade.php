@extends('layouts.app')
@section('title', 'Add Product')
@section('page-title', 'Add Medicine / Product')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-5xl">
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
            <div class="font-semibold mb-1">Please correct the following:</div>
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" id="productForm">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">SKU *</label>
                <input type="text" name="sku" value="{{ old('sku') }}" required class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Barcode</label>
                <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full border rounded-lg p-2">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Category</label>
                <select name="category_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Drug Type</label>
                <select name="drug_type_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($drugTypes as $d)
                        <option value="{{ $d->id }}" @selected(old('drug_type_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Form Type</label>
                <input type="text" name="form_type" value="{{ old('form_type') }}" placeholder="tablet/capsule/syrup" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Strength</label>
                <input type="text" name="strength" value="{{ old('strength') }}" placeholder="500mg" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Generic Name</label>
                <input type="text" name="generic_name" value="{{ old('generic_name') }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Manufacturer</label>
                <input type="text" name="manufacturer" value="{{ old('manufacturer') }}" class="w-full border rounded-lg p-2">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Purchase Unit (Box/Packet)</label>
                <select name="purchase_unit_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('purchase_unit_id') == $u->id)>{{ $u->name }} ({{ $u->short_name }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Pieces per Purchase Unit</label>
                <input id="piecesPerBox" type="number" name="pieces_per_purchase_unit" value="{{ old('pieces_per_purchase_unit', 1) }}" min="1" step="1" required class="w-full border rounded-lg p-2">
                <p class="mt-1 text-xs text-gray-500">Number of pieces contained in one box/packet</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Selling Unit</label>
                <select name="selling_unit_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('selling_unit_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Purchase Price (per box)</label>
                <input id="purchasePriceBox" type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price') }}" class="w-full border rounded-lg p-2">
            </div>

            {{-- Calculation-only field: no name attribute, therefore it is not submitted/saved. --}}
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                <label class="block text-sm font-semibold mb-1 text-blue-900">Selling Price (per box)</label>
                <input id="sellingPriceBox" type="number" step="0.01" min="0" class="w-full border border-blue-300 rounded-lg p-2 bg-white" autocomplete="off">
                <p class="mt-1 text-xs text-blue-600">Calculation only — not saved in database</p>
            </div>

            {{-- Calculation-only field: no name attribute, therefore it is not submitted/saved. --}}
            <div class="rounded-lg border border-purple-200 bg-purple-50 p-3">
                <label class="block text-sm font-semibold mb-1 text-purple-900">Percentage (%)</label>
                <input id="profitPercentage" type="number" step="0.01" min="0" class="w-full border border-purple-300 rounded-lg p-2 bg-white" autocomplete="off">
                <p class="mt-1 text-xs text-purple-600">Markup percentage — not saved in database</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Selling Price (per piece)</label>
                <input id="sellingPricePiece" type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price') }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">MRP</label>
                <input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp') }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Rack Number</label>
                <input type="text" name="rack_number" value="{{ old('rack_number') }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Min Stock Alert</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', 10) }}" min="0" class="w-full border rounded-lg p-2">
            </div>
        </div>

        <div class="mt-5 rounded-lg bg-gray-50 border p-3 text-sm text-gray-600">
            <strong>Calculation:</strong>
            Selling per piece = Selling per box ÷ Pieces per box;
            Percentage = ((Selling per box − Purchase per box) ÷ Selling per box) × 100.
        </div>

        <button class="mt-4 bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-semibold">Save Product</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const piecesInput = document.getElementById('piecesPerBox');
    const purchaseInput = document.getElementById('purchasePriceBox');
    const sellingBoxInput = document.getElementById('sellingPriceBox');
    const percentageInput = document.getElementById('profitPercentage');
    const sellingPieceInput = document.getElementById('sellingPricePiece');

    const numberValue = (element) => {
        const value = parseFloat(element.value);
        return Number.isFinite(value) ? value : null;
    };

    const money = value => (Math.round((value + Number.EPSILON) * 100) / 100).toFixed(2);
    const percent = value => (Math.round((value + Number.EPSILON) * 100) / 100).toFixed(2);

    // Selling box determines selling piece and, when purchase price exists, markup %.
    function calculateFromSellingBox() {
        const pieces = numberValue(piecesInput);
        const purchase = numberValue(purchaseInput);
        const sellingBox = numberValue(sellingBoxInput);

        if (sellingBox !== null && pieces !== null && pieces > 0) {
            sellingPieceInput.value = money(sellingBox / pieces);
        }
        if (sellingBox !== null && sellingBox > 0 && purchase !== null) {
            percentageInput.value = percent(((sellingBox - purchase) / sellingBox) * 100);
        }
    }

    // Percentage normally uses purchase price to create both selling prices.
    // If purchase price is empty but selling-box + percentage are entered,
    // it calculates purchase price backwards.
    function calculateFromPercentage() {
        const pieces = numberValue(piecesInput);
        const purchase = numberValue(purchaseInput);
        const sellingBox = numberValue(sellingBoxInput);
        const percentage = numberValue(percentageInput);

        if (percentage === null || percentage < 0 || percentage >= 100) return;

        if (purchase !== null && purchase >= 0) {
            // Example: purchase 1800 with 10% margin => selling box 2000.
            const newSellingBox = purchase / (1 - percentage / 100);
            sellingBoxInput.value = money(newSellingBox);
            if (pieces !== null && pieces > 0) {
                sellingPieceInput.value = money(newSellingBox / pieces);
            }
        } else if (sellingBox !== null) {
            // Example: selling box 2000 with 10% margin => purchase 1800.
            const newPurchase = sellingBox * (1 - percentage / 100);
            purchaseInput.value = money(newPurchase);
            if (pieces !== null && pieces > 0) {
                sellingPieceInput.value = money(sellingBox / pieces);
            }
        }
    }

    // Directly changing per-piece price keeps the temporary box/percentage fields in sync.
    function calculateFromSellingPiece() {
        const pieces = numberValue(piecesInput);
        const purchase = numberValue(purchaseInput);
        const sellingPiece = numberValue(sellingPieceInput);
        if (pieces === null || pieces <= 0 || sellingPiece === null) return;

        const sellingBox = sellingPiece * pieces;
        sellingBoxInput.value = money(sellingBox);
        if (purchase !== null && sellingBox > 0) {
            percentageInput.value = percent(((sellingBox - purchase) / sellingBox) * 100);
        }
    }

    sellingBoxInput.addEventListener('input', calculateFromSellingBox);
    percentageInput.addEventListener('input', calculateFromPercentage);
    sellingPieceInput.addEventListener('input', calculateFromSellingPiece);

    piecesInput.addEventListener('input', function () {
        if (numberValue(sellingBoxInput) !== null) calculateFromSellingBox();
        else calculateFromSellingPiece();
    });

    purchaseInput.addEventListener('input', function () {
        if (numberValue(sellingBoxInput) !== null) calculateFromSellingBox();
        else if (numberValue(percentageInput) !== null) calculateFromPercentage();
    });

    // Rebuild calculation-only values after a validation redirect if persisted values exist.
    if (numberValue(sellingPieceInput) !== null && numberValue(piecesInput) !== null) {
        calculateFromSellingPiece();
    }
});
</script>
@endpush
