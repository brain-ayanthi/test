@extends('layouts.app')
@section('title', 'Opening Stock Import')
@section('page-title', 'Bulk Opening Stock Import')

@section('content')
<div class="max-w-6xl mx-auto space-y-5">
    @if(session('import_errors'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">
            <div class="font-bold mb-2"><i class="fas fa-circle-exclamation mr-2"></i>Import එක සිදු නොවීය</div>
            <p class="text-sm mb-3">පහත errors නිවැරදි කර නැවත upload කරන්න. කිසිදු row එකක් database එකට save කර නැත.</p>
            <div class="max-h-64 overflow-y-auto rounded-lg bg-white border border-red-200 p-3">
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">
            <ul class="list-disc ml-5 text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border-l-4 border-red-500 bg-red-50 p-5 text-red-800">
        <div class="font-bold"><i class="fas fa-lock mr-2"></i>One-Time Opening Stock Import</div>
        <p class="mt-1 text-sm">
            මෙම පහසුකම system එක ආරම්භ කරන අවස්ථාවේ <strong>Opening Stock සඳහා පමණක්</strong> එක්වරක් භාවිතා කළ හැක.
            Import එක සාර්ථක වූ පසු button සහ සියලු import URLs ස්ථිරව lock වේ. Purchase stock add කිරීම සඳහා මෙය භාවිතා නොකරන්න.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">CSV File Upload</h2>
                    <p class="text-sm text-gray-500 mt-1">New Products + Batch Opening Stock එකවර create කරයි.</p>
                </div>
                <a href="{{ route('products.opening-stock.template') }}" class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    <i class="fas fa-download mr-1"></i> Download CSV Template
                </a>
            </div>

            <form method="POST" action="{{ route('products.opening-stock.import') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <label for="importFile" class="block border-2 border-dashed border-gray-300 hover:border-green-400 rounded-xl p-8 text-center cursor-pointer bg-gray-50 transition" id="dropArea">
                    <i class="fas fa-file-csv text-5xl text-green-500 mb-3"></i>
                    <div class="font-semibold text-gray-700">CSV file එක මෙතැනට select කරන්න</div>
                    <div class="text-xs text-gray-500 mt-1">Maximum file size: 5 MB</div>
                    <div id="selectedFile" class="mt-3 text-sm font-semibold text-blue-700"></div>
                    <input id="importFile" type="file" name="import_file" accept=".csv,text/csv" required class="hidden">
                </label>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <label class="flex items-start gap-2 text-sm text-gray-600">
                        <input type="checkbox" id="confirmImport" class="mt-1" required>
                        <span>Template columns වෙනස් කර නොමැති බවත් opening stock quantities පරීක්ෂා කළ බවත් තහවුරු කරමි.</span>
                    </label>
                    <div class="flex gap-2">
                        <a href="{{ route('products.index') }}" class="border px-5 py-2.5 rounded-lg font-semibold text-gray-600">Cancel</a>
                        <button type="submit" id="importButton" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold">
                            <i class="fas fa-upload mr-1"></i> Import Opening Stock
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-calculator text-purple-500 mr-2"></i>Price Calculation</h3>
            <div class="space-y-3 text-sm">
                <div class="rounded-lg bg-purple-50 p-3">
                    <div class="text-gray-500">Purchase Price / Box</div>
                    <code class="text-purple-800">Selling Box × (1 − Percentage/100)</code>
                </div>
                <div class="rounded-lg bg-blue-50 p-3">
                    <div class="text-gray-500">Selling Price / Piece</div>
                    <code class="text-blue-800">Selling Box ÷ Pieces per Box</code>
                </div>
                <div class="rounded-lg bg-green-50 p-3">
                    <div class="text-gray-500">Opening Stock Pieces</div>
                    <code class="text-green-800">Boxes × Pieces per Box + Loose Pieces</code>
                </div>
                <div class="border-t pt-3 text-xs text-gray-500">
                    Example: Selling Box Rs. 2,000 + 10% = Purchase Box Rs. 1,800.
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-bold text-gray-800 mb-4">CSV Columns</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-100 text-left"><th class="p-2">Column</th><th class="p-2">Required</th><th class="p-2">Description / Example</th></tr></thead>
                <tbody class="divide-y">
                    <tr><td class="p-2 font-mono">name, sku</td><td class="p-2 text-red-600 font-semibold">Yes</td><td class="p-2">Product name and unique SKU</td></tr>
                    <tr><td class="p-2 font-mono">pieces_per_purchase_unit</td><td class="p-2 text-red-600 font-semibold">Yes</td><td class="p-2">One box එකේ pieces ගණන, e.g. 100</td></tr>
                    <tr><td class="p-2 font-mono">selling_price_per_box, percentage</td><td class="p-2 text-red-600 font-semibold">Yes</td><td class="p-2">e.g. 2000 and 10</td></tr>
                    <tr><td class="p-2 font-mono">batch_number, expiry_date</td><td class="p-2 text-red-600 font-semibold">Yes</td><td class="p-2">Expiry format: YYYY-MM-DD</td></tr>
                    <tr><td class="p-2 font-mono">opening_boxes</td><td class="p-2 text-red-600 font-semibold">Yes</td><td class="p-2">Opening boxes quantity</td></tr>
                    <tr><td class="p-2 font-mono">opening_loose_pieces</td><td class="p-2">Optional value</td><td class="p-2">Loose stock නොමැති නම් 0</td></tr>
                    <tr><td class="p-2 font-mono">category, drug_type, units</td><td class="p-2">Optional value</td><td class="p-2">Database එකේ නැති values ස්වයංක්‍රීයව create වේ.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-4 rounded-lg border-l-4 border-yellow-400 bg-yellow-50 p-3 text-sm text-yellow-800">
            <strong>වැදගත්:</strong> Existing SKU එකක් තිබෙන row එකක් import නොවේ. Error එකක් තිබේ නම් සම්පූර්ණ file එක rollback වේ; partial import එකක් සිදු නොවේ.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('importFile');
    const selected = document.getElementById('selectedFile');
    const form = document.getElementById('importForm');
    const button = document.getElementById('importButton');

    fileInput.addEventListener('change', function () {
        selected.textContent = this.files.length ? this.files[0].name : '';
    });

    form.addEventListener('submit', function () {
        button.disabled = true;
        button.classList.add('opacity-60', 'cursor-not-allowed');
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Importing...';
    });
});
</script>
@endpush
