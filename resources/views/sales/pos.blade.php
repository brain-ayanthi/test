@extends('layouts.app')
@section('title', 'POS')
@section('page-title', 'Point of Sale')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <div class="flex gap-2 mb-4">
            <input type="text" id="prodSearch" onkeyup="filterProducts()" placeholder="Search medicine by name / barcode..." class="flex-1 border rounded-lg px-4 py-2">
        </div>
        <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-[60vh] overflow-y-auto">
            @foreach($products as $p)
            <div class="product-card border rounded-lg p-3 hover:shadow cursor-pointer" data-name="{{ strtolower($p->name) }}" onclick="addToCart({{ $p->id }}, '{{ $p->name }}', {{ $p->selling_price }})">
                <div class="font-semibold text-sm">{{ $p->name }}</div>
                <div class="text-xs text-gray-500">{{ $p->form_type }} {{ $p->strength }}</div>
                <div class="text-green-600 font-bold mt-1">Rs {{ number_format($p->selling_price, 2) }}</div>
                <div class="text-xs text-gray-400">Stock: {{ $p->stock_quantity }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Current Sale</h3>
        <div id="cartItems" class="space-y-2 mb-4 max-h-64 overflow-y-auto">
            <p class="text-gray-400 text-sm text-center py-4">No items added</p>
        </div>
        <hr class="my-3">
        <div class="space-y-1 text-sm mb-4">
            <div class="flex justify-between"><span>Subtotal</span><span id="cartSubtotal">0.00</span></div>
            <div class="flex justify-between"><span>Doctor Fee</span><input type="number" id="cartDocFee" value="0" class="w-20 border rounded px-1 text-right" onchange="calcCart()"></div>
            <div class="flex justify-between"><span>Discount</span><input type="number" id="cartDiscount" value="0" class="w-20 border rounded px-1 text-right" onchange="calcCart()"></div>
            <div class="flex justify-between text-lg font-bold border-t pt-1"><span>Total</span><span id="cartTotal" class="text-green-600">0.00</span></div>
        </div>
        <div class="space-y-2 mb-3">
            <select id="payMethod" class="w-full border rounded-lg p-2">
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="mfs">MFS</option>
            </select>
            <select id="payAccount" class="w-full border rounded-lg p-2">
                @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
            <input type="number" id="payAmount" placeholder="Paid amount" class="w-full border rounded-lg p-2" onchange="calcCart()">
        </div>
        <button onclick="checkout()" class="w-full bg-green-600 text-white py-3 rounded-lg font-bold"><i class="fas fa-cash-register mr-1"></i> Complete Sale</button>
    </div>
</div>

@push('scripts')
<script>
let cart = [];
function addToCart(id, name, price) {
    const ex = cart.find(c => c.id === id);
    if (ex) ex.qty++;
    else cart.push({id, name, price, qty: 1});
    renderCart();
}
function renderCart() {
    const box = document.getElementById('cartItems');
    if (!cart.length) { box.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">No items added</p>'; calcCart(); return; }
    box.innerHTML = cart.map((c,i) => `
        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
            <div class="flex-1">
                <div class="text-sm font-semibold">${c.name}</div>
                <div class="text-xs text-gray-500">Rs ${c.price} x ${c.qty}</div>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="changeQty(${i},-1)" class="w-6 h-6 bg-gray-200 rounded">-</button>
                <span class="w-6 text-center text-sm">${c.qty}</span>
                <button onclick="changeQty(${i},1)" class="w-6 h-6 bg-gray-200 rounded">+</button>
                <button onclick="removeItem(${i})" class="text-red-500 ml-1"><i class="fas fa-times"></i></button>
            </div>
        </div>`).join('');
    calcCart();
}
function changeQty(i,d){ cart[i].qty+=d; if(cart[i].qty<=0) cart.splice(i,1); renderCart(); }
function removeItem(i){ cart.splice(i,1); renderCart(); }
function calcCart() {
    let sub = cart.reduce((s,c) => s + c.price*c.qty, 0);
    let docFee = parseFloat(document.getElementById('cartDocFee').value)||0;
    let disc = parseFloat(document.getElementById('cartDiscount').value)||0;
    document.getElementById('cartSubtotal').textContent = sub.toFixed(2);
    document.getElementById('cartTotal').textContent = (sub+docFee-disc).toFixed(2);
}
function filterProducts() {
    const q = document.getElementById('prodSearch').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => {
        c.style.display = c.dataset.name.includes(q) ? '' : 'none';
    });
}
async function checkout() {
    if (!cart.length) return alert('Add items first');
    const items = cart.map(c => ({product_id:c.id, quantity:c.qty, unit_price:c.price}));
    const res = await fetch('{{ route("sales.pos.store") }}', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
        body: JSON.stringify({
            customer_type:'walking',
            payment_method: document.getElementById('payMethod').value,
            cash_account_id: document.getElementById('payAccount').value,
            paid_amount: document.getElementById('payAmount').value || 0,
            doctor_fee: document.getElementById('cartDocFee').value,
            discount: document.getElementById('cartDiscount').value,
            items
        })
    });
    const json = await res.json();
    if (json.success) { alert('Sale completed: '+json.sale.invoice_number); location.reload(); }
    else alert('Error completing sale');
}
</script>
@endpush
@endsection
