@extends('layouts.app')
@section('title', 'New Ready Treatment')
@section('page-title', 'Create Ready Treatment Template')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-4xl">
    <form method="POST" action="{{ route('treatments.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div><label class="block text-sm font-semibold mb-1">Template Name *</label><input type="text" name="name" required class="w-full border rounded-lg p-2" placeholder="e.g. Common Fever"></div>
            <div><label class="block text-sm font-semibold mb-1">Disease/Condition</label><input type="text" name="disease" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Doctor</label>
                <select name="doctor_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    @foreach($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="mb-2"><label class="block text-sm font-semibold">Description</label><textarea name="description" rows="2" class="w-full border rounded-lg p-2"></textarea></div>

        <h4 class="font-bold mt-4 mb-2">Medicines in Template</h4>
        <div id="items" class="space-y-2"></div>
        <button type="button" onclick="addRow()" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-plus mr-1"></i> Add Medicine</button>

        <div class="mt-6"><button class="bg-green-600 text-white px-6 py-2.5 rounded-lg font-semibold">Save Template</button>
        <a href="{{ route('treatments.index') }}" class="ml-2 text-gray-600">Cancel</a></div>
    </form>
</div>

@push('scripts')
<script>
const PRODUCTS = @json($products);
let idx=0;
function addRow(pid){
    const opts = PRODUCTS.map(p=>`<option value="${p.id}" ${pid==p.id?'selected':''}>${p.name} (${p.strength})</option>`).join('');
    const div=document.createElement('div');
    div.className='grid grid-cols-1 md:grid-cols-6 gap-2 items-center bg-gray-50 p-2 rounded';
    div.innerHTML=`
        <select name="items[${idx}][product_id]" required class="border rounded p-2 md:col-span-2"><option value="">Select medicine</option>${opts}</select>
        <select name="items[${idx}][timing]" class="border rounded p-2">
            <option value="OD">OD (1x)</option><option value="BD">BD (2x)</option>
            <option value="TDS" selected>TDS (3x)</option><option value="QID">QID (4x)</option>
        </select>
        <input type="number" name="items[${idx}][duration_days]" value="3" min="1" class="border rounded p-2" placeholder="Days">
        <select name="items[${idx}][meal_relation]" class="border rounded p-2 text-sm"><option value="">Meal</option><option value="before">Before</option><option value="after">After</option></select>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500"><i class="fas fa-times"></i></button>`;
    document.getElementById('items').appendChild(div);
    idx++;
}
addRow();
</script>
@endpush
@endsection
