@extends('layouts.app')
@section('title', 'New Purchase')
@section('page-title', 'Create Purchase Invoice')

@section('content')
<form method="POST" action="{{ route('purchases.store') }}">
@csrf
<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1">Vendor *</label>
            <select name="vendor_id" required class="w-full border rounded-lg p-2">
                <option value="">-- Select Vendor --</option>
                @foreach($vendors as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-semibold mb-1">Purchase Date *</label>
            <input type="date" name="purchase_date" value="{{ now()->toDateString() }}" required class="w-full border rounded-lg p-2"></div>
        <div><label class="block text-sm font-semibold mb-1">Due Date</label>
            <input type="date" name="due_date" class="w-full border rounded-lg p-2"></div>
        <div><label class="block text-sm font-semibold mb-1">Reference</label>
            <input type="text" name="reference" class="w-full border rounded-lg p-2"></div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <h3 class="font-bold mb-3">Purchase Items (Box / Packet to Piece conversion)</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm" id="purchaseTable">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2">Medicine</th>
                <th class="p-2">Purchase Unit</th>
                <th class="p-2">Pcs/Unit</th>
                <th class="p-2">Qty (boxes)</th>
                <th class="p-2">Total Pcs</th>
                <th class="p-2">Price/Box</th>
                <th class="p-2">Line Total</th>
                <th class="p-2">Sell Price/pc</th>
                <th class="p-2">Batch No.</th>
                <th class="p-2">Expiry</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody id="purchaseRows"></tbody>
        <tfoot>
            <tr class="font-bold bg-gray-50">
                <td colspan="6" class="text-right p-2">Subtotal:</td>
                <td class="p-2" id="subtotalCell">Rs 0.00</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
    </div>
    <datalist id="productList"></datalist>
    <button type="button" onclick="addPurchaseRow()" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-plus"></i> Add Row</button>
</div>

<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-semibold mb-1">Discount</label><input type="number" step="0.01" name="discount" id="discount" value="0" min="0" class="w-full border rounded-lg p-2 calc-trigger"></div>
            <input type="hidden" name="payment_status" value="pending">
        </div>

        {{-- Totals summary --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-bold" id="sumSubtotal">Rs 0.00</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-600">Discount</span>
                <span class="font-bold text-red-600" id="sumDiscount">Rs 0.00</span>
            </div>
            <div class="flex justify-between py-3 text-lg font-bold border-b-2">
                <span>Grand Total</span>
                <span class="text-green-700" id="sumTotal">Rs 0.00</span>
            </div>
            <div class="flex justify-between py-2 pt-3">
                <span class="text-gray-600">Due Amount</span>
                <span class="font-bold text-red-600 text-lg" id="sumDue">Rs 0.00</span>
            </div>
        </div>
    </div>

    <button class="mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold"><i class="fas fa-save mr-1"></i> Save Purchase & Update Stock</button>
</div>
</form>

@push('scripts')
<script>
let products = @json($products);
let idx = 0;

function addPurchaseRow() {
    const row = document.createElement('tr');
    row.className = 'border-b purchase-row';
    row.innerHTML = `
        <td class="p-2">
            <input type="text" list="productList" required placeholder="Search medicine..." class="border rounded p-1 w-56 product-search" oninput="onProductSearch(this)" onfocus="this.select()">
            <input type="hidden" name="items[${idx}][product_id]" class="product-id">
        </td>
        <td class="p-2 unit-cell">-</td>
        <td class="p-2"><input type="number" name="items[${idx}][pieces_per_unit]" value="1" min="1" class="ppu-input border rounded p-1 w-20" oninput="calcRow(this)"></td>
        <td class="p-2"><input type="number" name="items[${idx}][purchase_quantity]" value="1" min="1" class="qty-input border rounded p-1 w-20" oninput="calcRow(this)"></td>
        <td class="p-2 pcs-cell font-bold text-blue-600">1</td>
        <td class="p-2"><input type="number" step="0.01" name="items[${idx}][purchase_price]" value="0" min="0" class="price-input border rounded p-1 w-24" oninput="calcRow(this)"></td>
        <td class="p-2 line-total font-bold">Rs 0.00</td>
        <td class="p-2"><input type="number" step="0.01" name="items[${idx}][selling_price]" value="0" min="0" class="border rounded p-1 w-24"></td>
        <td class="p-2"><input type="text" name="items[${idx}][batch_number]" required class="border rounded p-1 w-28" placeholder="Batch"></td>
        <td class="p-2"><input type="date" name="items[${idx}][expiry_date]" required class="border rounded p-1"></td>
        <td class="p-2"><button type="button" onclick="removeRow(this)" class="text-red-500"><i class="fas fa-times"></i></button></td>
    `;
    document.getElementById('purchaseRows').appendChild(row);
    idx++;
    calcTotals();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calcTotals();
}

function onProductSearch(input) {
    const val = input.value.trim();
    const row = input.closest('tr');
    const match = products.find(p => p.name.toLowerCase() === val.toLowerCase()
        || p.name.toLowerCase().startsWith(val.toLowerCase()));
    const hiddenId = row.querySelector('.product-id');
    if (match) {
        hiddenId.value = match.id;
        row.querySelector('.ppu-input').value = match.ppu || 1;
        row.querySelector('.unit-cell').textContent = match.unit || '-';
        row.querySelector('input[name$="[purchase_price]"]').value = match.pprice || 0;
        row.querySelector('input[name$="[selling_price]"]').value = match.sprice || 0;
        calcRow(input);
    } else {
        hiddenId.value = '';
    }
}

function calcRow(el) {
    const row = el.closest('tr');
    const ppu = parseFloat(row.querySelector('.ppu-input').value) || 0;
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const pcs = ppu * qty;
    row.querySelector('.pcs-cell').textContent = pcs;
    const lineTotal = qty * price;
    row.querySelector('.line-total').textContent = 'Rs ' + lineTotal.toFixed(2);
    calcTotals();
}

function calcTotals() {
    let subtotal = 0;
    document.querySelectorAll('.purchase-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        subtotal += qty * price;
    });

    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const grand = subtotal - discount;

    const fmt = n => 'Rs ' + (n || 0).toFixed(2);

    document.getElementById('subtotalCell').textContent = fmt(subtotal);
    document.getElementById('sumSubtotal').textContent = fmt(subtotal);
    document.getElementById('sumDiscount').textContent = fmt(discount);
    document.getElementById('sumTotal').textContent = fmt(grand);
    document.getElementById('sumDue').textContent = fmt(grand > 0 ? grand : 0);
}

document.addEventListener('DOMContentLoaded', function () {
    const dl = document.getElementById('productList');
    if (dl) {
        products.forEach(p => {
            const o = document.createElement('option');
            o.value = p.name;
            dl.appendChild(o);
        });
    }
    document.querySelectorAll('.calc-trigger').forEach(el => {
        el.addEventListener('input', calcTotals);
    });
    addPurchaseRow();
});
</script>
@endpush
@endsection
