@extends('layouts.app')
@section('title', 'Edit Ready Treatment')
@section('page-title', 'Edit Ready Treatment Template')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-5xl">
    <form method="POST" action="/treatments/{{ $treatment->id }}" id="templateForm">
        @csrf
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="deleted_items" id="deletedItems" value="">

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
            <strong>Please fix the following:</strong>
            <ul class="list-disc ml-5 mt-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $treatment->name) }}" required class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Disease/Condition</label>
                <input type="text" name="disease" value="{{ old('disease', $treatment->disease) }}" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Doctor</label>
                <select name="doctor_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" {{ old('doctor_id', $treatment->doctor_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full border rounded-lg p-2">{{ old('description', $treatment->description) }}</textarea>
        </div>

        {{-- Medicines --}}
        <div class="border-t pt-4 mt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-lg"><i class="fas fa-pills mr-1 text-blue-600"></i>Medicines in Template</h3>
                <button type="button" onclick="addMedicineRow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    <i class="fas fa-plus mr-1"></i> Add Medicine
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="itemsTable">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Medicine</th>
                            <th class="p-2 w-28">Timing</th>
                            <th class="p-2 w-20">Days</th>
                            <th class="p-2 w-28">Meal</th>
                            <th class="p-2 w-48">Instruction</th>
                            <th class="p-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($treatment->items as $idx => $item)
                        <tr class="border-b medicine-row" data-id="{{ $item->id }}">
                            <td class="p-2">
                                <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}">
                                <select name="items[{{ $idx }}][product_id]" required class="w-full border rounded p-2 text-sm product-select" onchange="onProductChange(this)">
                                    <option value="">-- Select medicine --</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-form="{{ $p->form_type }}" data-strength="{{ $p->strength }}"
                                        {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs text-gray-500 mt-1 product-info">
                                    {{ $item->form_type }} {{ $item->strength }}
                                </div>
                            </td>
                            <td class="p-2">
                                <select name="items[{{ $idx }}][timing]" required class="w-full border rounded p-2 text-sm">
                                    @foreach(['OD','BD','TDS','QID'] as $t)
                                    <option value="{{ $t }}" {{ $item->timing == $t ? 'selected' : '' }}>{{ $t }} ({{ ['OD'=>1,'BD'=>2,'TDS'=>3,'QID'=>4][$t] }}x)</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-2">
                                <input type="number" name="items[{{ $idx }}][duration_days]" value="{{ $item->duration_days }}" min="1" required class="w-full border rounded p-2 text-sm">
                            </td>
                            <td class="p-2">
                                <select name="items[{{ $idx }}][meal_relation]" class="w-full border rounded p-2 text-sm">
                                    <option value="">-</option>
                                    <option value="before" {{ $item->meal_relation == 'before' ? 'selected' : '' }}>Before</option>
                                    <option value="after" {{ $item->meal_relation == 'after' ? 'selected' : '' }}>After</option>
                                </select>
                            </td>
                            <td class="p-2">
                                <input type="text" name="items[{{ $idx }}][instruction]" value="{{ $item->instruction }}" class="w-full border rounded p-2 text-sm" placeholder="e.g. Take after meals">
                            </td>
                            <td class="p-2 text-center">
                                <button type="button" onclick="removeRow(this, {{ $item->id }})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-2">At least one medicine is required.</p>
        </div>

        <div class="flex gap-2 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                <i class="fas fa-save mr-1"></i> Update Template
            </button>
            <a href="/treatments" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold">Cancel</a>
        </div>
    </form>
</div>

<script>
let rowIndex = {{ $treatment->items->count() }};
const deletedIds = [];

const products = @json($products);

function addMedicineRow() {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.className = 'border-b medicine-row';
    let options = '<option value="">-- Select medicine --</option>';
    products.forEach(p => {
        options += `<option value="${p.id}" data-form="${p.form_type||''}" data-strength="${p.strength||''}">${p.name}</option>`;
    });
    tr.innerHTML = `
        <td class="p-2">
            <select name="items[${rowIndex}][product_id]" required class="w-full border rounded p-2 text-sm product-select" onchange="onProductChange(this)">
                ${options}
            </select>
            <div class="text-xs text-gray-500 mt-1 product-info"></div>
        </td>
        <td class="p-2">
            <select name="items[${rowIndex}][timing]" required class="w-full border rounded p-2 text-sm">
                <option value="OD">OD (1x)</option>
                <option value="BD">BD (2x)</option>
                <option value="TDS" selected>TDS (3x)</option>
                <option value="QID">QID (4x)</option>
            </select>
        </td>
        <td class="p-2">
            <input type="number" name="items[${rowIndex}][duration_days]" value="3" min="1" required class="w-full border rounded p-2 text-sm">
        </td>
        <td class="p-2">
            <select name="items[${rowIndex}][meal_relation]" class="w-full border rounded p-2 text-sm">
                <option value="">-</option>
                <option value="before">Before</option>
                <option value="after">After</option>
            </select>
        </td>
        <td class="p-2">
            <input type="text" name="items[${rowIndex}][instruction]" class="w-full border rounded p-2 text-sm" placeholder="e.g. Take after meals">
        </td>
        <td class="p-2 text-center">
            <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
        </td>`;
    tbody.appendChild(tr);
    rowIndex++;
}

function onProductChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = sel.closest('tr').querySelector('.product-info');
    if (info) info.textContent = (opt.dataset.form || '') + ' ' + (opt.dataset.strength || '');
}

function removeRow(btn, existingId) {
    if (existingId) {
        deletedIds.push(existingId);
        document.getElementById('deletedItems').value = deletedIds.join(',');
    }
    const row = btn.closest('tr');
    row.parentNode.removeChild(row);
}
</script>
@endsection
