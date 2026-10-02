@extends('layouts.app')
@section('title', 'Create Prescription')
@section('page-title', 'Dashboard')
@php($patientHistoryEndpoint = route('patients.prescription-history', ['patient' => '__PATIENT__']))

@push('styles')
<style>
/* Sidebar patient panel */
.side-panel { transform: translateX(100%); transition: transform .3s ease; }
.side-panel.open { transform: translateX(0); }
.backdrop { opacity:0; pointer-events:none; transition:opacity .25s; }
.backdrop.show { opacity:1; pointer-events:auto; }

/* Drug search dropdown */
.drug-dropdown { max-height: 320px; overflow-y:auto; }
.drug-option:hover, .drug-option.active { background:#eff6ff; }
.drug-option { cursor:pointer; }

/* Medicine row */
.med-row { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; }
.timing-badge { font-size:11px; padding:2px 8px; border-radius:9999px; font-weight:700; }

/* Drug type chips */
.drug-chip { cursor:pointer; transition: transform .15s; }
.drug-chip:hover { transform: translateY(-2px); }
.drug-chip.active { outline: 3px solid #facc15; outline-offset: 2px; }

/* Patient tab */
.patient-tab { cursor:pointer; transition: all .15s; }
.patient-tab.active { background:#2563eb; color:#fff; border-color:#2563eb; }

/* Scrollbar */
.thin-scroll::-webkit-scrollbar { width:6px; height:6px; }
.thin-scroll::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }

/* Radiology tag */
.rad-tag { display:inline-flex; align-items:center; gap:6px; background:#dbeafe; color:#1e40af; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:600; }
.rad-tag button { color:#1e40af; }

/* Selected-patient history; does not restyle or rebuild the Create form. */
#rxCreateBody[hidden], #patientHistoryPanel[hidden], #patientHistorySelect[hidden] { display:none!important; }
#patientHistoryPanel { color:#334155; min-width:0; }
.rxh-header,.rxh-actions,.rxh-pagination,.rxh-card-heading,.rxh-card-subtitle { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.rxh-header { margin:4px 0 12px; }.rxh-header h3 { font-size:20px; font-weight:700; color:#1e293b; }.rxh-header p { margin-top:4px; font-size:14px; color:#2563eb; }
.rxh-button { padding:7px 12px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer; }
.rxh-button:hover { background:#eff6ff; }.rxh-button:disabled { opacity:.4; cursor:not-allowed; }.rxh-select { margin-top:12px; color:#2563eb; }
.rxh-draft-note { border:1px solid #bfdbfe; background:#eff6ff; color:#1e40af; border-radius:8px; padding:10px 12px; font-size:12px; margin-bottom:14px; }
#patientHistoryStatus { font-size:13px; margin:12px 0; }
.rxh-record { border:1px solid #e2e8f0; border-radius:10px; margin:12px 0; background:#fff; overflow:hidden; }
.rxh-record summary { padding:14px 16px; cursor:pointer; list-style:none; }.rxh-record summary::-webkit-details-marker { display:none; }.rxh-record summary:hover { background:#f8fafc; }
.rxh-record[open] summary { background:#f8fafc; border-bottom:1px solid #e2e8f0; }.rxh-record summary:focus-visible { outline:2px solid #2563eb; outline-offset:-2px; }
.rxh-card-heading>div { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }.rxh-number { color:#2563eb; font-family:monospace; font-size:15px; }.rxh-date { font-size:12px; color:#64748b; }
.rxh-badge { background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:20px; font-size:11px; text-transform:capitalize; }.rxh-active { background:#dcfce7; color:#166534; }.rxh-completed { background:#dbeafe; color:#1e40af; }.rxh-cancelled { background:#fee2e2; color:#991b1b; }
.rxh-total { font-weight:700; color:#0f766e; font-size:14px; }.rxh-card-subtitle { margin-top:8px; font-size:12px; color:#64748b; }
.rxh-record-body { padding:14px 16px; }.rxh-visit+.rxh-visit { border-top:1px dashed #cbd5e1; padding-top:16px; margin-top:16px; }.rxh-visit-heading { font-size:13px; margin-bottom:10px; }
.rxh-notes { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-bottom:12px; }.rxh-note { font-size:12px; }.rxh-note strong { color:#64748b; }.rxh-note p,.rxh-instruction { white-space:pre-wrap; overflow-wrap:anywhere; }
.rxh-table-wrap { width:100%; overflow-x:auto; }.rxh-table { width:100%; min-width:680px; border-collapse:collapse; font-size:12px; }.rxh-table th { text-align:left; background:#f1f5f9; padding:9px; }.rxh-table td { padding:9px; border-bottom:1px solid #e2e8f0; vertical-align:top; }.rxh-table small { display:block; color:#64748b; margin-top:3px; }.rxh-instruction { min-width:140px; max-width:280px; }
.rxh-tests { background:#eff6ff; color:#1e40af; border-radius:7px; padding:10px; margin-top:12px; font-size:12px; }.rxh-tests>div { display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-top:7px; }.rxh-tests p { width:100%; white-space:pre-wrap; }
.rxh-fees { display:flex; flex-wrap:wrap; gap:10px 16px; margin-top:14px; font-size:12px; }.rxh-footnote { font-size:11px; color:#64748b; margin-top:12px; }.rxh-pagination { margin-top:16px; font-size:12px; color:#64748b; }

</style>
@endpush

@section('content')
<div class="flex flex-col lg:flex-row gap-5">
    <div class="flex-1 min-w-0 bg-white rounded-xl shadow-sm p-5">
        <div class="flex border-b mb-4 overflow-x-auto">
            <button type="button" id="prescriptionCreateTab" aria-controls="rxCreateBody" class="tab-btn active px-4 py-2 text-gray-600 border-b-2 border-transparent font-semibold text-blue-600 border-blue-600"><i class="fas fa-prescription mr-1"></i> Prescription Create</button>
            <button type="button" id="patientPrescriptionHistoryTab" aria-controls="patientHistoryPanel" class="tab-btn px-4 py-2 text-gray-600 border-b-2 border-transparent font-medium"><i class="fas fa-history mr-1"></i> Prescription History</button>
            <button class="tab-btn px-4 py-2 text-gray-600 border-b-2 border-transparent font-medium"><i class="fas fa-sticky-note mr-1"></i> Note</button>
            <button class="tab-btn px-4 py-2 text-gray-600 border-b-2 border-transparent font-medium"><i class="fas fa-stream mr-1"></i> Timeline</button>
        </div>

        {{-- Patient tabs (multiple patients in one Rx) --}}
        <div class="mb-4">
            <div id="patientTabs" class="flex flex-wrap gap-2 mb-3"></div>
            <button type="button" onclick="openPatientPanel()" class="text-blue-600 text-sm font-semibold hover:underline"><i class="fas fa-plus-circle mr-1"></i> Add Patient to Prescription</button>
        </div>

        <div id="rxCreateBody">
        {{-- Drug search (requirement #1, #2) --}}
        <div class="mb-4 relative" id="drugSearchWrap">
            <div class="flex gap-2 items-center">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="drugSearch" autocomplete="off" oninput="onDrugSearch()" onfocus="onDrugSearch()"
                           class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm" placeholder="Drag Name / Search drug...">
                    <div id="drugDropdown" class="hidden drug-dropdown absolute z-30 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg thin-scroll"></div>
                </div>
                <select id="drugTypeFilter" onchange="filterDrugType(this.value)" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    @foreach($drugTypes as $dt)
                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="addEmptyMedicineRow()" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">Drug ADD</button>
            </div>
            <div class="flex flex-wrap gap-2 mt-3" id="drugTypeChips">
                @foreach($drugTypes as $dt)
                <span class="drug-chip px-3 py-2 rounded-lg text-sm font-semibold text-white shadow" style="background:{{ $dt->color }}" data-type="{{ $dt->id }}" onclick="quickDrugType({{ $dt->id }}, this)">
                    <i class="fas {{ $dt->icon ?: 'fa-capsules' }} mr-1"></i>{{ $dt->name }}
                </span>
                @endforeach
            </div>
        </div>

        {{-- Medicines table (no Time column; Number of Time = timing; Instruction added) --}}
        <div class="overflow-x-auto thin-scroll">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-left">
                        <th class="p-2 w-[28%]">Medicine</th>
                        <th class="p-2">Avl. Qty</th>
                        <th class="p-2">Form Type</th>
                        <th class="p-2">Strength</th>
                        <th class="p-2">Meal</th>
                        <th class="p-2">Number of Time</th>
                        <th class="p-2">Days</th>
                        <th class="p-2">Qty</th>
                        <th class="p-2">Price</th>
                        <th class="p-2">Instruction</th>
                        <th class="p-2">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="medicineRows"></tbody>
            </table>
        </div>

        {{-- Lab Workup / Physiotherapy removed per requirement #6, #7 --}}
        {{-- Diagnosis / precaution per selected patient --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Diagnosis</label>
                <textarea id="diagInput" rows="3" class="w-full border rounded-lg p-2 text-sm" placeholder="Diagnosis..."></textarea>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Precaution/Notes</label>
                <textarea id="precInput" rows="3" class="w-full border rounded-lg p-2 text-sm" placeholder="Precautions..."></textarea>
            </div>
        </div>

        {{-- Radiology (requirement #8) --}}
        <div class="mt-4 p-4 border border-dashed border-blue-300 bg-blue-50 rounded-lg">
            <label class="block font-semibold text-blue-900 mb-2"><i class="fas fa-x-ray mr-1"></i> Radiology / Extra Tests</label>
            <div class="flex gap-2 flex-wrap items-start">
                <select id="radSelect" class="border rounded-lg px-3 py-2 text-sm flex-1 min-w-[200px]">
                    <option value="">-- Select Radiology Test --</option>
                    @foreach($radiologyTests as $rt)
                    <option value="{{ $rt->id }}" data-price="{{ $rt->price }}" data-name="{{ $rt->name }}">{{ $rt->name }} (Rs {{ number_format($rt->price,2) }})</option>
                    @endforeach
                </select>
                <button type="button" onclick="addRadiology()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-plus mr-1"></i>Add Test</button>
            </div>
            <div id="radTags" class="flex flex-wrap gap-2 mt-3"></div>
            <p class="text-xs text-blue-700 mt-2" id="radTotalLine">Radiology total: <strong>Rs 0.00</strong></p>
        </div>

        {{-- Doctor & Ready Treatment --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Doctor</label>
                <select id="doctorSelect" class="w-full border rounded-lg p-2">
                    <option value="">-- Select Doctor --</option>
                    @foreach($doctors as $d)
                    <option value="{{ $d->id }}" data-fee="{{ $d->doctor_fee }}">{{ $d->name }} ({{ $d->specialization }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Ready Treatment (Template)</label>
                <select id="rtSelect" onchange="applyTemplate(this.value)" class="w-full border rounded-lg p-2">
                    <option value="">-- Select Ready Treatment --</option>
                    @foreach($readyTreatments as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('treatments.index') }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block"><i class="fas fa-cog mr-1"></i>Manage templates</a>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 flex-wrap">
            <button type="button" id="saveBtn" onclick="savePrescription('complete')" class="bg-green-600 hover:bg-green-700 text-white px-8 py-2.5 rounded-lg font-semibold text-lg"><i class="fas fa-check mr-1"></i> Save & Complete (Print)</button>
        </div>
        </div> {{-- rxCreateBody: hide, never destroy/rebuild when viewing History --}}
        <section id="patientHistoryPanel" hidden data-history-version="patient-history-v1" aria-label="Selected patient prescription history">
            <div class="rxh-header">
                <div><h3>Prescription History</h3><p id="patientHistoryName">No patient selected</p></div>
                <div class="rxh-actions"><button type="button" id="patientHistoryRefresh" class="rxh-button">Refresh</button><button type="button" id="patientHistoryBack" class="rxh-button">Back to Create</button></div>
            </div>
            <p class="rxh-draft-note">Your current prescription draft stays on the Create tab. This history is read-only and belongs to the selected patient above.</p>
            <p id="patientHistoryStatus" class="text-gray-500" role="status" aria-live="polite"></p>
            <button type="button" id="patientHistorySelect" class="rxh-button rxh-select">Select Patient</button>
            <div id="patientHistoryResults"></div>
            <div class="rxh-pagination"><span id="patientHistoryPage"></span><div class="rxh-actions"><button type="button" id="patientHistoryPrev" class="rxh-button" disabled>Previous</button><button type="button" id="patientHistoryNext" class="rxh-button" disabled>Next</button></div></div>
        </section>
    </div>

    {{-- Right: Patient Details (first patient) --}}
    <div class="w-full lg:w-80 flex-shrink-0">
        <div id="patientPanel" class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-gray-400 py-10">
                <i class="fas fa-user-injured text-5xl mb-3"></i>
                <p class="font-semibold text-gray-600">No patient selected</p>
                <button onclick="openPatientPanel()" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-users mr-1"></i>Select Patient</button>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 mt-4">
            <h3 class="font-bold text-gray-800 mb-3">Fee Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-600">Medicine Cost</span><span id="sumMedicine" class="font-semibold">0.00</span></div>
                <div class="flex justify-between text-red-600 font-semibold"><span><i class="fas fa-x-ray mr-1"></i>Radiology (extra)</span><span id="sumRadiology">0.00</span></div>
                <div class="flex justify-between items-center"><span class="text-gray-600">Doctor Fee</span><input type="number" id="docFee" value="0" min="0" class="w-24 border rounded px-2 py-1 text-right" onchange="updateTotals()"></div>
                <div class="flex justify-between items-center"><span class="text-gray-600">Discount</span><input type="number" id="disc" value="0" min="0" class="w-24 border rounded px-2 py-1 text-right" onchange="updateTotals()"></div>
                <div class="border-t pt-2 flex justify-between text-lg font-bold"><span>Total Fee</span><span id="sumTotal" class="text-green-600">0.00</span></div>
            </div>
            <p class="text-xs text-gray-400 mt-2"><i class="fas fa-info-circle"></i> Medicine cost auto-calculated (not editable).</p>
        </div>
    </div>
</div>

{{-- ====== SIDEBAR PATIENT PANEL (requirements #1, #10) ====== --}}
<div id="backdrop" class="backdrop fixed inset-0 bg-black/50 z-40" onclick="closePatientPanel()"></div>
<div id="sidePanel" class="side-panel fixed top-0 right-0 h-full w-full sm:w-[440px] bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-arrow-left mr-2 text-gray-500"></i>Patient List</h2>
        <button onclick="closePatientPanel()" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
    </div>
    <div class="p-4 border-b">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="pSearch" onkeyup="renderPatientList()" placeholder="Search Patient..." class="w-full pl-10 pr-4 py-2 border rounded-lg">
        </div>
    </div>
    <div id="pList" class="flex-1 overflow-y-auto p-2 thin-scroll"></div>
    <div class="p-4 border-t">
        <button onclick="showCreatePatient()" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold">Create Patient</button>
    </div>
</div>

{{-- ====== CREATE PATIENT SUB-PANEL ====== --}}
<div id="createPanel" class="hidden fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-xl font-bold text-gray-800">Patient Create</h2>
        <button onclick="closeCreatePatient()" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
    </div>
    <form id="createPatientForm" class="p-5 space-y-3 flex-1 overflow-y-auto" onsubmit="submitPatient(event)">
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Name</label><input type="text" name="name" required class="w-full border rounded-lg px-3 py-2" placeholder="Name"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Email</label><input type="email" name="email" class="w-full border rounded-lg px-3 py-2" placeholder="Email"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Phone</label><input type="text" name="phone" class="w-full border rounded-lg px-3 py-2" placeholder="Phone"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Age (Year)</label><input type="number" name="age" class="w-full border rounded-lg px-3 py-2" placeholder="Age (Year)"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Gender</label>
            <select name="gender" class="w-full border rounded-lg px-3 py-2"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
        </div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Address</label><textarea name="address" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea></div>
        <button type="submit" class="w-full bg-blue-900 text-white py-3 rounded-lg font-bold text-lg">Save</button>
        <button type="button" onclick="closeCreatePatient()" class="w-full bg-yellow-400 text-gray-900 py-3 rounded-lg font-bold text-lg"><i class="fas fa-angle-double-left"></i> Back</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/clinicms-print-server.js') }}"></script>
<script>
const PRODUCTS = @json($products);
const RADIOLOGY = @json($radiologyTests);
const PATIENTS = @json($allPatients);

let prescriptionPatients = [];   // [{patient, doctorId, doctorFee, items:[], radiologies:[], diagnosis, precautions}]
let activePatientIdx = 0;
let medRowIdx = 0;
let radId = 0;

// ============ PRESCRIPTION NUMBER & BARCODE ============
// The number is generated by the server so the side panel and the
// popup after save always show the same value.
const SERVER_RX_NUMBER = @json($nextRxNumber ?? null);

function getRxNumber(){
    if(window.__currentRxNumber){
        return window.__currentRxNumber;
    }
    if(SERVER_RX_NUMBER){
        window.__currentRxNumber = SERVER_RX_NUMBER;
        return SERVER_RX_NUMBER;
    }
    // Fallback only (should not normally be used).
    const d = new Date();
    const ymd = d.getFullYear() +
        String(d.getMonth()+1).padStart(2,'0') +
        String(d.getDate()).padStart(2,'0');
    return 'RX-' + ymd + '-01';
}

function setRxNumber(num){
    window.__currentRxNumber = num;
    refreshRxDisplay();
}

function refreshRxDisplay(){
    const num = getRxNumber();
    const disp = document.getElementById('rxNumberDisplay');
    if(disp) disp.textContent = num;
    const bar = document.getElementById('rxBarcode');
    if(bar) bar.innerHTML = renderBarcode(num);
}

// Simple Code128-style SVG barcode (crisp, with text underneath)
function renderBarcode(text){
    let bits = '1101';
    for(let i=0;i<text.length;i++){
        bits += text.charCodeAt(i).toString(2).padStart(8,'0');
    }
    bits += '1101';
    const bw = 1.6, h = 44;
    let x = 0, rects = '';
    for(let i=0;i<bits.length;i++){
        if(bits[i]==='1'){
            rects += '<rect x="'+x+'" y="0" width="'+bw+'" height="'+h+'" fill="#000"/>';
        }
        x += bw;
    }
    return '<svg viewBox="0 0 '+x+' '+(h+16)+'" width="'+x+'" height="'+(h+16)+'" xmlns="http://www.w3.org/2000/svg">'
        + rects
        + '<text x="'+(x/2)+'" y="'+(h+12)+'" text-anchor="middle" font-family="monospace" font-size="11" fill="#111">'+text+'</text>'
        + '</svg>';
}

// ============ SIDE PANEL (Patient List) ============
function openPatientPanel(){
    document.getElementById('sidePanel').classList.add('open');
    document.getElementById('backdrop').classList.add('show');
    renderPatientList();
}
function closePatientPanel(){
    document.getElementById('sidePanel').classList.remove('open');
    document.getElementById('backdrop').classList.remove('show');
}
function showCreatePatient(){ document.getElementById('createPanel').classList.remove('hidden'); }
function closeCreatePatient(){ document.getElementById('createPanel').classList.add('hidden'); }

function renderPatientList(){
    const q = (document.getElementById('pSearch').value||'').toLowerCase();
    const list = PATIENTS.filter(p => p.name.toLowerCase().includes(q) || (p.phone||'').includes(q) || p.patient_code.toLowerCase().includes(q));
    document.getElementById('pList').innerHTML = list.map(p=>`
        <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 cursor-pointer border-b" onclick="selectPatient(${p.id})">
            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 flex-shrink-0"><i class="fas fa-user text-2xl"></i></div>
            <div class="min-w-0"><div class="font-bold text-gray-800 truncate">${p.name}</div><div class="text-sm text-gray-500">${p.phone||''}</div></div>
        </div>`).join('') || '<p class="text-gray-400 text-center py-6">No patients found</p>';
}

function selectPatient(id, forceAdd){
    saveActivePatientState();
    const p = PATIENTS.find(x=>x.id===id);
    if(!p) return;
    const existingIdx = prescriptionPatients.findIndex(pp=>pp.patient.id===id);
    // If already added, just switch to that patient's tab (UNLESS this is a brand-new patient)
    if(existingIdx !== -1 && !forceAdd){
        activePatientIdx = existingIdx;
        renderAll();
        closePatientPanel();
        return;
    }
    prescriptionPatients.push({
        patient: p,
        doctorId: document.getElementById('doctorSelect').value || null,
        doctorFee: parseFloat(document.getElementById('docFee').value)||0,
        discount: 0,
        diagnosis: '',
        precautions: '',
        items: [],
        radiologies: []
    });
    activePatientIdx = prescriptionPatients.length - 1;
    renderAll();
    closePatientPanel();
}

async function submitPatient(e){
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));

    // Age must be a number (not empty string) for the backend validation
    if(data.age === '') delete data.age;

    let res;
    try {
        res = await fetch(document.querySelector('meta[name=patients-store-url]').content, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
            body: JSON.stringify(data)
        });
    } catch (netErr) {
        alert('Network error: ' + netErr.message);
        return;
    }

    // Try to parse JSON (Laravel returns JSON because of Accept: application/json)
    let json = null;
    const text = await res.text();
    try { json = JSON.parse(text); } catch (e) {}

    if(res.ok && json && json.success && json.patient){
        // Brand-new patient — add forcefully (bypass duplicate check)
        PATIENTS.unshift(json.patient);
        closeCreatePatient();
        selectPatient(json.patient.id, true);
        e.target.reset();
    } else {
        // Show the real validation error from the server
        let msg = 'Could not create patient.';
        if(json){
            if(json.message) msg += '\n\n' + json.message;
            if(json.errors){
                msg += '\n\nValidation errors:\n';
                for(const field in json.errors){
                    msg += ' - ' + json.errors[field].join(', ') + '\n';
                }
            }
        } else {
            msg += '\n\nServer response (not JSON):\n' + text.substring(0, 500);
        }
        alert(msg);
    }
}

// ============ RENDER PATIENT TABS + PANEL + MEDICINES ============
function renderAll(){
    renderPatientTabs();
    renderPatientPanel();
    renderMedicineRows();
    renderRadiology();
    renderDiagnosis();
    updateTotals();
    window.ClinicPatientHistory?.patientChanged();
}

function renderPatientTabs(){
    const el = document.getElementById('patientTabs');
    if(prescriptionPatients.length===0){ el.innerHTML=''; return; }
    el.innerHTML = prescriptionPatients.map((pp,i)=>`
        <div onclick="switchPatient(${i})" class="patient-tab flex items-center gap-2 px-4 py-2 border-2 rounded-lg text-sm font-semibold ${i===activePatientIdx?'active':''}">
            <i class="fas fa-user"></i>
            <span class="max-w-[140px] truncate">${pp.patient.name}</span>
            ${prescriptionPatients.length>1 ? `<button onclick="event.stopPropagation();removePatient(${i})" class="ml-1 hover:text-red-300">&times;</button>`:''}
        </div>`).join('');
}
function switchPatient(i){
    saveActivePatientState();
    activePatientIdx=i;
    renderAll();
}
function removePatient(i){
    if(!confirm('Remove this patient from prescription?')) return;
    saveActivePatientState();
    const previouslyActive = prescriptionPatients[activePatientIdx];
    prescriptionPatients.splice(i,1);
    const keptIndex = prescriptionPatients.indexOf(previouslyActive);
    activePatientIdx = keptIndex >= 0 ? keptIndex : Math.min(i, prescriptionPatients.length-1);
    renderAll();
}
function saveActivePatientState(){
    const pp = prescriptionPatients[activePatientIdx];
    if(!pp) return;
    pp.doctorId = document.getElementById('doctorSelect').value;
    pp.doctorFee = parseFloat(document.getElementById('docFee').value)||0;
    pp.discount = parseFloat(document.getElementById('disc').value)||0;
    pp.diagnosis = document.getElementById('diagInput').value;
    pp.precautions = document.getElementById('precInput').value;
    pp.items = collectMedicineRows();
    // Keep pp.radiologies intact: it is maintained by addRadiology/removeRadiology.
    // Do not lose test IDs/notes or change numeric temp IDs by scraping DOM tags.
}

function renderPatientPanel(){
    const el = document.getElementById('patientPanel');
    const pp = prescriptionPatients[activePatientIdx];
    if(!pp){
        el.innerHTML = `
        <div class="text-center mb-4">
            <div class="text-gray-400 py-6"><i class="fas fa-user-injured text-5xl mb-2"></i><p class="font-semibold text-gray-600">No patient selected</p></div>
            <button onclick="openPatientPanel()" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-users mr-1"></i>Select Patient</button>
        </div>
        `;
        return;
    }
    const p = pp.patient;
    el.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-gray-800">Patient Details</h3>
            <button onclick="openPatientPanel()" class="text-blue-600 text-sm hover:underline"><i class="fas fa-exchange-alt"></i> Switch</button>
        </div>
        <div class="text-center mb-4">
            <div class="w-24 h-24 mx-auto bg-gray-200 rounded-full flex items-center justify-center text-gray-400 mb-2"><i class="fas fa-user text-5xl"></i></div>
            <h4 class="font-bold text-lg text-gray-800">${p.name}</h4>
            <p class="text-sm text-gray-500">${p.age||'-'} years old, ${p.gender||'-'}</p>
            <p class="text-xs text-gray-400">Last Visited N/A</p>
        </div>
        <div class="text-sm space-y-1 border-t pt-3 text-left">
            <p><i class="fas fa-phone w-5 text-gray-400"></i> ${p.phone||'-'}</p>
            <p><i class="fas fa-envelope w-5 text-gray-400"></i> ${p.email||'-'}</p>
        </div>
        <div class="mt-4 p-3 bg-blue-50 rounded-lg text-center">
            <div class="text-xs text-blue-700 font-semibold mb-1">Prescription ID (Barcode)</div>
            <div class="font-mono font-bold text-blue-900 text-lg" id="rxNumberDisplay">${getRxNumber()}</div>
            <div class="flex justify-center mt-2">
                <div class="bg-white p-2 rounded" id="rxBarcode">${renderBarcode(getRxNumber())}</div>
            </div>
            <p class="text-xs text-blue-700 mt-1">All patients share this Rx ID</p>
        </div>`;
}

// ============ MEDICINE ROWS ============
// Strength-aware quantity rules:
// 500mg -> 250mg means half a tablet per dose; 5ml means 5ml per dose.
function parseStrengthValue(value){
    const text = String(value || '').toLowerCase().replace(/μ/g, 'µ');
    const match = text.match(/(\d+(?:\.\d+)?)\s*(mcg|µg|mg|ml|g|l)\b/i);
    if(!match) return null;
    let amount = parseFloat(match[1]);
    let unit = match[2].toLowerCase();
    if(unit === 'µg') unit = 'mcg';
    if(unit === 'mcg'){ amount *= 0.001; unit = 'mg'; }
    if(unit === 'g'){ amount *= 1000; unit = 'mg'; }
    if(unit === 'l'){ amount *= 1000; unit = 'ml'; }
    return {amount, unit};
}

function defaultDoseStrength(product){
    if(!product) return '';
    const raw = String(product.strength || '').trim();
    const form = String(product.form_type || '').toLowerCase();
    const isLiquid = /(syrup|suspension|liquid|solution|drops|mixture)/.test(form);
    if(isLiquid){
        const volumeMatches = [...raw.matchAll(/(\d+(?:\.\d+)?)\s*(ml|l)\b/ig)];
        if(volumeMatches.length){
            const m = volumeMatches[volumeMatches.length - 1];
            return `${m[1]}${m[2].toLowerCase()}`;
        }
    }
    return raw;
}

function calculateDoseQuantity(item, product){
    const mult = {OD:1,BD:2,BID:2,TDS:3,QID:4}[item.timing] || 1;
    const days = parseFloat(item.duration_days) || 0;
    const doses = mult * days;
    const prescribed = parseStrengthValue(item.strength);

    // A volume strength is the volume taken at every dose.
    // Example: 5ml × TDS × 5 days = 75ml.
    if(prescribed && prescribed.unit === 'ml'){
        return prescribed.amount * doses;
    }

    // Mass strengths on tablets/capsules are calculated as a fraction of the
    // product's original strength. Example: 250mg / 500mg = 0.5 tablet/dose.
    const original = parseStrengthValue(product?.strength);
    if(prescribed && original && prescribed.unit === 'mg' && original.unit === 'mg' && original.amount > 0){
        return (prescribed.amount / original.amount) * doses;
    }

    return doses;
}

function formatDoseQty(value){
    const rounded = Math.round((Number(value) + Number.EPSILON) * 1000) / 1000;
    return Number.isInteger(rounded) ? String(rounded) : String(rounded).replace(/0+$/, '').replace(/\.$/, '');
}
function formatDoseQtyDisplay(value, strength){
    const parsed = parseStrengthValue(strength);
    return formatDoseQty(value) + (parsed?.unit === 'ml' ? ' ml' : '');
}

function collectMedicineRows(){
    const rows = [];
    document.querySelectorAll('#medicineRows .med-row').forEach(tr=>{
        const sel = tr.querySelector('.drug-sel');
        if(!sel || !sel.value) return;
        rows.push({
            product_id: parseInt(sel.value),
            strength: tr.querySelector('.strength-inp').value.trim(),
            timing: tr.querySelector('.timing-sel').value,
            duration_days: parseFloat(tr.querySelector('.days-inp').value),
            meal_relation: tr.querySelector('.meal-sel').value,
            instruction_note: tr.querySelector('.instr-inp').value,
            unit_price: parseFloat(tr.querySelector('.price-inp').value)||0,
        });
    });
    return rows;
}

function renderMedicineRows(){
    const pp = prescriptionPatients[activePatientIdx];
    const tbody = document.getElementById('medicineRows');
    tbody.innerHTML='';
    if(!pp){ return; }
    if(pp.items.length===0){ addEmptyMedicineRow(); return; }
    pp.items.forEach((it,idx)=> addMedicineRow(it, idx));
}

function addEmptyMedicineRow(){
    if(!prescriptionPatients[activePatientIdx]){ alert('Please select a patient first.'); openPatientPanel(); return; }
    const pp = prescriptionPatients[activePatientIdx];
    const newItem = {product_id:null, strength:'', timing:'TDS', duration_days:3, meal_relation:'', instruction_note:'', unit_price:0};
    pp.items.push(newItem);
    addMedicineRow(newItem, pp.items.length-1);
}

function addMedicineRow(item, idx){
    const tbody = document.getElementById('medicineRows');
    const tr = document.createElement('tr');
    tr.className='med-row';
    const opts = PRODUCTS.map(p=>`<option value="${p.id}" data-form="${p.form_type||''}" data-strength="${p.strength||''}" data-price="${p.selling_price}" data-stock="${p.stock_quantity??'?'}" ${item.product_id==p.id?'selected':''}>${p.name}</option>`).join('');
    const timingMap = {OD:1,BD:2,TDS:3,QID:4};
    const timingOpts = ['OD','BD','TDS','QID'].map(t=>`<option value="${t}" ${item.timing===t?'selected':''}>${t} (${timingMap[t]}x)</option>`).join('');
    const selected = PRODUCTS.find(p=>p.id===item.product_id);
    const prescribedStrength = item.strength || defaultDoseStrength(selected);
    item.strength = prescribedStrength;
    const qty = calculateDoseQuantity(item, selected);
    const total = qty * (item.unit_price||0);

    tr.innerHTML = `
        <td class="p-2">
            <input type="hidden" class="drug-sel" value="${item.product_id||''}">
            <div class="font-semibold text-gray-800 drug-name">${selected ? selected.name : '-'}</div>
        </td>
        <td class="p-2"><span class="avl-qty px-2 py-0.5 rounded text-xs font-bold ${(selected?.stock_quantity??0)<=0?'bg-red-100 text-red-700':(selected?.stock_quantity??0)<=10?'bg-amber-100 text-amber-700':'bg-green-100 text-green-700'}">${selected?.stock_quantity ?? '-'}</span></td>
        <td class="p-2 form-type text-gray-600 text-xs">${selected?.form_type||'-'}</td>
        <td class="p-2">
            <input type="text" class="strength-inp border rounded px-2 py-1 w-20 text-xs font-semibold text-purple-700"
                   value="${prescribedStrength}" placeholder="500mg / 5ml" title="Original: ${selected?.strength||'-'}"
                   oninput="calcRow(this)">
            <div class="text-[10px] text-gray-400 mt-1">Base: ${selected?.strength||'-'}</div>
        </td>
        <td class="p-2"><select class="meal-sel border rounded px-1 py-1 text-xs"><option value="">-</option><option value="before" ${item.meal_relation==='before'?'selected':''}>Before</option><option value="after" ${item.meal_relation==='after'?'selected':''}>After</option></select></td>
        <td class="p-2"><select class="timing-sel border rounded px-2 py-1" onchange="calcRow(this)">${timingOpts}</select></td>
        <td class="p-2"><input type="number" class="days-inp border rounded px-2 py-1 w-16" value="${item.duration_days||3}" min="1" onchange="calcRow(this)"></td>
        <td class="p-2 qty-disp font-bold text-blue-600">${formatDoseQtyDisplay(qty, prescribedStrength)}</td>
        <td class="p-2"><input type="number" step="0.01" class="price-inp border rounded px-2 py-1 w-20" value="${item.unit_price||0}" onchange="calcRow(this)"></td>
        <td class="p-2"><input type="text" class="instr-inp border rounded px-2 py-1 w-32 text-xs" placeholder="Note..." value="${item.instruction_note||''}"></td>
        <td class="p-2 row-tot font-bold text-gray-800">${total.toFixed(2)}</td>
        <td class="p-2"><button type="button" onclick="removeMedicineRow(this)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button></td>`;
    tbody.prepend(tr);  // requirement #2: new row at top
}

function calcRow(el){
    const row=el.closest('tr');
    const productId = parseInt(row.querySelector('.drug-sel').value);
    const product = PRODUCTS.find(p=>p.id===productId);
    const item = {
        strength: row.querySelector('.strength-inp').value.trim(),
        timing: row.querySelector('.timing-sel').value,
        duration_days: parseFloat(row.querySelector('.days-inp').value)||0,
    };
    const price=parseFloat(row.querySelector('.price-inp').value)||0;
    const qty=calculateDoseQuantity(item, product);
    row.querySelector('.qty-disp').textContent=formatDoseQtyDisplay(qty, item.strength);
    row.querySelector('.row-tot').textContent=(qty*price).toFixed(2);
    updateTotals();
}
function removeMedicineRow(btn){
    btn.closest('tr').remove();
    updateTotals();
}

// ============ DRUG SEARCH DROPDOWN (requirement #2) ============
let drugTypeFilter = '';
function onDrugSearch(){
    const q = document.getElementById('drugSearch').value.trim().toLowerCase();
    const dd = document.getElementById('drugDropdown');
    if(q.length < 2){ dd.classList.add('hidden'); return; }
    let list = PRODUCTS.filter(p => p.name.toLowerCase().includes(q));
    if(drugTypeFilter) list = list.filter(p=>String(p.drug_type_id)===drugTypeFilter);
    if(!list.length){ dd.innerHTML='<div class="p-3 text-gray-400 text-sm">No medicines found</div>'; dd.classList.remove('hidden'); return; }
    dd.innerHTML = list.slice(0,30).map(p=>`
        <div class="drug-option p-2 border-b flex justify-between items-center" onclick="pickDrugFromSearch(${p.id})">
            <div><div class="font-semibold text-sm">${p.name}</div><div class="text-xs text-gray-500">${p.form_type||''} ${p.strength||''}</div></div>
            <div class="text-right"><div class="font-bold text-green-600 text-sm">Rs ${p.selling_price}</div><div class="text-xs ${p.stock_quantity<=0?'text-red-500':'text-gray-400'}">Stock: ${p.stock_quantity}</div></div>
        </div>`).join('');
    dd.classList.remove('hidden');
}
function pickDrugFromSearch(pid){
    if(!prescriptionPatients[activePatientIdx]){ alert('Select a patient first'); openPatientPanel(); return; }
    document.getElementById('drugSearch').value='';
    document.getElementById('drugDropdown').classList.add('hidden');
    const p = PRODUCTS.find(x=>x.id===pid);
    prescriptionPatients[activePatientIdx].items.unshift({
        product_id:p.id, strength:defaultDoseStrength(p), timing:'TDS', duration_days:3,
        meal_relation:'', instruction_note:'', unit_price:parseFloat(p.selling_price)
    });
    renderMedicineRows();
    updateTotals();
}
function filterDrugType(tid){ drugTypeFilter = tid; onDrugSearch(); }
function quickDrugType(tid, el){
    document.querySelectorAll('.drug-chip').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    drugTypeFilter = String(tid);
    document.getElementById('drugTypeFilter').value = tid;
    document.getElementById('drugSearch').focus();
}
document.addEventListener('click', e=>{
    if(!document.getElementById('drugSearchWrap').contains(e.target))
        document.getElementById('drugDropdown').classList.add('hidden');
});

// ============ RADIOLOGY ============
function addRadiology(){
    const sel = document.getElementById('radSelect');
    if(!sel.value){ alert('Select a test'); return; }
    const pp = prescriptionPatients[activePatientIdx];
    if(!pp){ alert('Select a patient first'); return; }
    const opt = sel.options[sel.selectedIndex];
    pp.radiologies.push({temp_id: ++radId, test_name: opt.dataset.name, price: parseFloat(opt.dataset.price)});
    renderRadiology();
    updateTotals();
    sel.value='';
}
function removeRadiology(tid){
    const pp = prescriptionPatients[activePatientIdx];
    pp.radiologies = pp.radiologies.filter(r=>r.temp_id!==tid);
    renderRadiology();
    updateTotals();
}
function renderRadiology(){
    const pp = prescriptionPatients[activePatientIdx];
    const el = document.getElementById('radTags');
    const totalEl = document.getElementById('radTotalLine');
    if(!pp){ el.innerHTML=''; totalEl.innerHTML='Radiology total: <strong>Rs 0.00</strong>'; return; }
    el.innerHTML = pp.radiologies.map(r=>`<span class="rad-tag" data-tid="${r.temp_id}" data-name="${r.test_name}" data-price="${r.price}">${r.test_name} (Rs ${r.price.toFixed(2)})<button type="button" onclick="removeRadiology(${r.temp_id})">&times;</button></span>`).join('');
    const total = pp.radiologies.reduce((s,r)=>s+r.price,0);
    totalEl.innerHTML = `Radiology total: <strong>Rs ${total.toFixed(2)}</strong>`;
}

function renderDiagnosis(){
    const pp = prescriptionPatients[activePatientIdx];
    document.getElementById('diagInput').value = pp?.diagnosis||'';
    document.getElementById('precInput').value = pp?.precautions||'';
    document.getElementById('docFee').value = pp?.doctorFee||0;
    document.getElementById('disc').value = pp?.discount||0;
    document.getElementById('doctorSelect').value = pp?.doctorId||'';
}

// ============ TOTALS ============
function updateTotals(){
    let med=0, rad=0;
    prescriptionPatients.forEach((pp,i)=>{
        if(i===activePatientIdx){
            saveActivePatientState();
        }
        pp.items.forEach(it=>{
            const product = PRODUCTS.find(p=>p.id===it.product_id);
            const qty = calculateDoseQuantity(it, product);
            med += qty*(parseFloat(it.unit_price)||0);
        });
        pp.radiologies.forEach(r=> rad += parseFloat(r.price)||0);
    });
    const doc = prescriptionPatients.reduce((s,pp)=>s+(parseFloat(pp.doctorFee)||0),0);
    const disc = prescriptionPatients.reduce((s,pp)=>s+(parseFloat(pp.discount)||0),0);
    document.getElementById('sumMedicine').textContent = med.toFixed(2);
    document.getElementById('sumRadiology').textContent = rad.toFixed(2);
    document.getElementById('sumTotal').textContent = (med+rad+doc-disc).toFixed(2);
}

// ============ READY TREATMENT (AJAX) ============
async function applyTemplate(rtId){
    if(!rtId) return;
    if(!prescriptionPatients[activePatientIdx]){ alert('Select a patient first'); document.getElementById('rtSelect').value=''; return; }
    try{
        const res = await fetch(`/treatments/${rtId}/data`, {headers:{'Accept':'application/json'}});
        if(!res.ok) throw new Error('Template not found');
        const tpl = await res.json();
        const pp = prescriptionPatients[activePatientIdx];
        let added = 0;
        (tpl.items||[]).forEach(it=>{
            const prod = PRODUCTS.find(p=>p.id===it.product_id);
            if(prod){
                pp.items.push({
                    product_id: prod.id, strength:defaultDoseStrength(prod),
                    timing: it.timing, duration_days: it.duration_days,
                    meal_relation: it.meal_relation, instruction_note: it.instruction||'',
                    unit_price: parseFloat(prod.selling_price)
                });
                added++;
            }
        });
        renderMedicineRows();
        updateTotals();
        alert(`Template "${tpl.name}" applied - ${added} medicine(s) added.`);
    }catch(e){
        alert('Could not load template: ' + e.message);
    }
    document.getElementById('rtSelect').value='';
}

// ============ SAVE ============
async function savePrescription(action){
    if(!prescriptionPatients.length){ alert('Please add at least one patient.'); openPatientPanel(); return; }

    // Instant UI feedback so the button feels fast
    const btn = document.getElementById('saveBtn');
    if(btn){
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    }

    try {
    saveActivePatientState();
    for(const pp of prescriptionPatients){
        if(!pp.items.length){ alert(`Patient ${pp.patient.name} has no medicines.`); return; }
        const fee = parseFloat(pp.doctorFee)||0;
        if(fee <= 0){
            if(!confirm(`Patient ${pp.patient.name} has no doctor fee. Continue saving?`)) return;
        }
    }
    const payload = {
        prescription_date: new Date().toISOString().slice(0,10),
        patients: prescriptionPatients.map(pp=>({
            patient_id: pp.patient.id,
            doctor_id: pp.doctorId,
            doctor_fee: pp.doctorFee,
            discount: pp.discount,
            diagnosis: pp.diagnosis,
            precautions: pp.precautions,
            items: pp.items.map(it=>{
                const product = PRODUCTS.find(p=>p.id===it.product_id);
                const qty = calculateDoseQuantity(it, product);
                const price = parseFloat(it.unit_price)||0;
                return {
                    product_id: it.product_id,
                    strength: it.strength || defaultDoseStrength(product),
                    timing: it.timing,
                    duration_days: it.duration_days,
                    quantity: qty,
                    unit_price: price,
                    total: qty * price,
                    meal_relation: it.meal_relation,
                    instruction_note: it.instruction_note,
                };
            }),
            radiologies: pp.radiologies.map(r=>({
                radiology_test_id: r.radiology_test_id||null,
                test_name: r.test_name,
                price: r.price,
            }))
        }))
    };
    const res = await fetch(document.querySelector('meta[name=prescriptions-store-url]').content, {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
        body: JSON.stringify(payload)
    });
    if(res.ok){
        const json = await res.json();
        if(json.prescription){
            const rxNo = json.prescription.prescription_number;
            const rxId = json.prescription.id;
            // The saved prescription gets the number the server already showed.
            setRxNumber(rxNo);
            showRxCreatedPopup(rxNo, rxId, json.prescription);
        } else {
            window.location.href = '/prescriptions/create';
        }
    } else {
        let msg = 'Error saving prescription.';
        try {
            const err = await res.json();
            if(err.message) msg += '\n' + err.message;
            if(err.errors){
                msg += '\n';
                for(const k in err.errors) msg += '\n- ' + err.errors[k].join(', ');
            }
        } catch(e){}
        alert(msg);
    }
    } finally {
        if(btn){
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Save & Complete (Print)';
        }
    }
}

// ============ SUCCESS POPUP (after save) ============
function showRxCreatedPopup(rxNo, rxId, prescription){
    // Remove if already exists
    const old = document.getElementById('rxPopupOverlay');
    if(old) old.remove();

    const overlay = document.createElement('div');
    overlay.id = 'rxPopupOverlay';
    overlay.className = 'fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4 overflow-y-auto';

    // Build patient sections
    let patientHtml = '';
    // The loaded relation is camelCase ("prescriptionPatients") from Eloquent.
    const patients = prescription
        ? (prescription.prescription_patients || prescription.prescriptionPatients || [])
        : [];
    if(patients.length){
        patients.forEach((pp, idx) => {
            let itemsHtml = '';
            (pp.items || []).forEach((it, i) => {
                const prescribedStrength = it.strength || (it.product ? it.product.strength : '') || '';
                const formType = it.product && it.product.form_type ? it.product.form_type : (it.form_type || '');
                const drugLabel = (it.drug_name || '') + (formType || prescribedStrength ? ' ['+[formType, prescribedStrength].filter(Boolean).join(' ')+']' : '');
                const quantityLabel = formatDoseQtyDisplay(it.quantity, prescribedStrength);
                itemsHtml += `
                <tr class="border-b">
                    <td class="p-1 text-xs align-top">${i+1}</td>
                    <td class="p-1 text-xs font-semibold">
                        ${drugLabel}
                        <div class="text-[10px] text-gray-500">${it.timing} x ${it.duration_days}d</div>
                    </td>
                    <td class="p-1 text-xs text-center align-top font-bold">${quantityLabel}</td>
                </tr>`;
            });
            let radHtml = '';
            (pp.radiologies || []).forEach(r => {
                radHtml += `<div class="text-xs text-amber-700 pl-2">[R] ${r.test_name}</div>`;
            });
            patientHtml += `
            <div class="border rounded mb-3 bg-gray-50">
                <div class="bg-blue-100 px-2 py-1 flex justify-between text-sm">
                    <strong>Patient ${idx+1}: ${pp.patient_name}</strong>
                    <span>Doctor Fee: Rs ${parseFloat(pp.doctor_fee||0).toFixed(2)}</span>
                </div>
                <table class="w-full">
                    <thead><tr class="bg-white text-xs">
                        <th class="p-1 text-left w-6">#</th>
                        <th class="p-1 text-left">Medicine</th>
                        <th class="p-1 text-right w-12">Qty</th>
                    </tr></thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
                ${radHtml}
            </div>`;
        });
    }

    overlay.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-3xl w-full my-8">
            <div class="text-center mb-4">
                <div class="w-14 h-14 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Prescription Saved!</h2>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 mb-4 text-center">
                <div class="text-xs text-blue-700 font-semibold">Prescription ID / Barcode</div>
                <div class="font-mono font-bold text-blue-900 text-2xl my-2">${rxNo}</div>
                <div class="flex justify-center bg-white p-2 rounded inline-flex" id="popupBarcode"></div>
            </div>

            <div class="mb-2 flex justify-between items-center">
                <h3 class="font-bold text-gray-700"><i class="fas fa-list-alt mr-1"></i> Prescription Preview</h3>
                <span class="text-xs text-gray-500">If details are wrong, go back and correct</span>
            </div>
            <div class="max-h-80 overflow-y-auto border rounded p-2 mb-4 bg-white">
                ${patientHtml || '<p class="text-gray-400 text-center text-sm py-4">No patient data.</p>'}
            </div>

            <div class="flex justify-end mb-2">
                <button id="popupPrinterSetupBtn" type="button" class="text-xs text-blue-700 hover:underline">
                    <i class="fas fa-server mr-1"></i> Print Server Setup / Pair
                </button>
            </div>
            <div class="flex gap-2">
                <button id="popupBackBtn" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white py-3 rounded-lg font-bold">
                    <i class="fas fa-edit mr-1"></i> Edit Prescription
                </button>
                <button id="popupPrintBtn" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-bold">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <button id="popupOkBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold">
                    New Rx
                </button>
            </div>
        </div>`;
    document.body.appendChild(overlay);
    document.getElementById('popupBarcode').innerHTML = renderBarcode(rxNo);

    document.getElementById('popupOkBtn').addEventListener('click', () => {
        window.location.href = '/prescriptions/create';
    });
    document.getElementById('popupPrintBtn').addEventListener('click', async function () {
        // The standalone ClinicMS Print Server EXE prints silently through the
        // selected Windows XP-80T driver. No browser print popup is opened.
        await ClinicMSPrintServer.printPrescription(rxId, rxNo, prescription, this);
    });
    document.getElementById('popupPrinterSetupBtn').addEventListener('click', async function () {
        await ClinicMSPrintServer.openSetup();
    });
    document.getElementById('popupBackBtn').addEventListener('click', () => {
        // Close the popup so the user can edit the form they just filled.
        const overlay = document.getElementById('rxPopupOverlay');
        if(overlay) overlay.remove();
        // Scroll back to the top where the prescription form is.
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
}

// Tab handlers are installed by ClinicPatientHistory below.
</script>
<script src="{{ asset('js/patient-prescription-history.js') }}?v=20260927-1"></script>
<script>
ClinicPatientHistory.init({
    endpoint: @json($patientHistoryEndpoint),
    getPatient: () => prescriptionPatients[activePatientIdx]?.patient || null,
    selectPatient: () => openPatientPanel()
});
</script>
@endpush
