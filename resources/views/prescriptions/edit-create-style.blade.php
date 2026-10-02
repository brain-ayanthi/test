@extends('layouts.app')
@section('title', 'Edit Prescription')
@section('page-title', 'Edit Prescription')

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
/* Edit-only additions; base layout follows the supplied Create view. */
#medicineRows input:disabled { background:#f1f5f9; color:#64748b; }
#medicineRows .med-row { border-bottom:1px solid #e5e7eb; }
#medicineRows td { vertical-align:top; }
#medicineRows .drug-label { min-width:170px; }
#editorFieldset { min-width:0; }
#rxPopupOverlay[hidden] { display:none; }
.side-panel:not(.open) { visibility:hidden; }
.side-panel.open { visibility:visible; }
#sidePanel button:focus-visible, .patient-tab:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
.rx-error-line { white-space:pre-wrap; }
</style>
@endpush

@section('content')
<!-- CLINICMS_CREATE_STYLE_EDIT_V3 -->
<div class="flex flex-wrap justify-between items-center gap-3 mb-4">
    <div><h2 class="font-bold text-xl text-gray-800">Edit Prescription <span class="font-mono text-blue-600">{{ $prescription->prescription_number }}</span></h2>
    <p class="text-xs text-gray-500 mt-1">Update this prescription, not a new Rx. Existing stock safeguards remain active.</p></div>
    <div class="flex gap-2"><a href="{{ route('prescriptions.edit', $prescription) }}" class="border bg-white rounded-lg px-3 py-2 text-sm">Reload saved data</a>
    <a href="{{ route('prescriptions.index') }}" class="bg-white border rounded-lg px-3 py-2 text-sm">All Prescriptions</a></div>
</div>
<div id="editErrors" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-4" role="alert" tabindex="-1"></div>
@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-4"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div id="legacyWarning" class="{{ count($legacyIds) ? '' : 'hidden' }} bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 mb-4 text-sm">
    <i class="fas fa-lock mr-1"></i> Historical stock: marked medicines cannot have their product/quantity changed or be removed because full batch records are unavailable. Other details can be edited.
</div>
<noscript><div class="bg-red-50 text-red-700 p-4">JavaScript is required to edit this prescription.</div></noscript>
<fieldset id="editorFieldset">
<div class="flex flex-col lg:flex-row gap-5">
    <div class="flex-1 min-w-0 bg-white rounded-xl shadow-sm p-5">
        <div class="flex border-b mb-4 overflow-x-auto gap-1">
            <button type="button" data-tab="prescription" onclick="showEditorTab('prescription')" class="tab-btn px-4 py-2 border-b-2 font-semibold text-blue-600 border-blue-600 whitespace-nowrap"><i class="fas fa-prescription mr-1"></i> Prescription Edit</button>
            <a href="{{ route('prescriptions.index') }}" class="px-4 py-2 text-gray-600 whitespace-nowrap"><i class="fas fa-history mr-1"></i> Prescription History</a>
            <a href="{{ route('prescriptions.show', $prescription) }}" class="px-4 py-2 text-gray-600 whitespace-nowrap"><i class="fas fa-file-medical mr-1"></i> View Prescription</a>
            <button type="button" data-tab="payment" onclick="showEditorTab('payment')" class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-600 whitespace-nowrap"><i class="fas fa-credit-card mr-1"></i> Payment</button>
            <button type="button" data-tab="note" onclick="showEditorTab('note')" class="tab-btn px-4 py-2 border-b-2 border-transparent text-gray-600 whitespace-nowrap"><i class="fas fa-sticky-note mr-1"></i> Note</button>
        </div>
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label class="text-sm font-semibold text-gray-600">Prescription date <input type="date" id="prescriptionDate" required class="border rounded-lg px-3 py-2 ml-2"></label>
            <span class="text-xs bg-blue-50 text-blue-700 rounded-full px-3 py-1">Editing existing record #{{ $prescription->id }}</span>
            <span id="rxLayoutVersion" class="text-xs text-blue-700 border border-blue-100 rounded-full px-3 py-1">Create-style Edit · v3</span>
            <span class="text-xs text-gray-500" id="saveState">Saved data loaded</span>
        </div>
        <div id="prescriptionSection">
        {{-- Patient tabs (multiple patients in one Rx) --}}
        <div class="mb-4">
            <div id="patientTabs" class="flex flex-wrap gap-2 mb-3"></div>
            <button type="button" onclick="openPatientPanel()" class="text-blue-600 text-sm font-semibold hover:underline"><i class="fas fa-plus-circle mr-1"></i> Add Patient to Prescription</button>
        </div>

        {{-- Drug search --}}
        <div class="mb-4 relative" id="drugSearchWrap">
            <div class="flex flex-wrap sm:flex-nowrap gap-2 items-center">
                <div class="w-full sm:w-auto sm:flex-1 min-w-0 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="drugSearch" autocomplete="off" role="combobox" aria-label="Search medicines" aria-controls="drugDropdown" aria-expanded="false" oninput="onDrugSearch()" onfocus="onDrugSearch()"
                           class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm" placeholder="Drug name / Search medicine...">
                    <div id="drugDropdown" role="listbox" class="hidden drug-dropdown absolute z-30 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg thin-scroll"></div>
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
                <span class="drug-chip px-3 py-2 rounded-lg text-sm font-semibold text-white shadow" style="background:{{ preg_match('/^#[0-9a-fA-F]{3,8}$/', $dt->color ?? '') ? $dt->color : '#2563eb' }}" data-type="{{ $dt->id }}" onclick="quickDrugType({{ $dt->id }}, this)">
                    <i class="fas {{ $dt->icon ?: 'fa-capsules' }} mr-1"></i>{{ $dt->name }}
                </span>
                @endforeach
            </div>
        </div>

        <div id="replaceNotice" class="hidden text-sm text-blue-800 bg-blue-50 rounded-lg p-2 mb-2"></div>
        <p class="text-xs text-gray-500 mb-3">Quantity is entered in stock units and is preserved on load. Changing strength/timing/days does not automatically change it. Available quantity excludes expired stock and does not include stock already reserved by this prescription.</p>
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
                        <th class="p-2">Discount</th>
                        <th class="p-2">Instruction</th>
                        <th class="p-2">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="medicineRows"></tbody>
            </table>
        </div>

        {{-- Prescription-level fields are preserved in the Note tab. --}}
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

        <div class="mt-3"><label class="text-sm font-semibold text-gray-700">Next visit / follow-up</label><input id="nextVisitInput" maxlength="1000" class="w-full border rounded-lg p-2 text-sm" placeholder="Follow-up instructions..."></div>

        {{-- Radiology --}}
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
            <button type="button" onclick="addCustomRadiology()" class="text-xs text-blue-700 underline mt-2">+ Custom test</button>
            <div id="radTags" class="space-y-2 mt-3"></div>
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

        <p class="text-xs text-gray-500 mt-2">Templates add medicines; they never replace existing rows. Review the quantity of each added medicine before saving.</p>
        </div>
        <div id="paymentSection" class="hidden bg-blue-50 border border-blue-100 rounded-xl p-5">
            <h3 class="font-bold text-blue-900 mb-3">Payment summary</h3>
            <p class="text-sm text-gray-600 mb-4">Payments are read-only in this editor. Use the clinic's payment/refund workflow to change the amount paid.</p>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>Amount paid<strong class="block text-xl" id="paymentPaid"></strong></div>
                <div>New total (preview)<strong class="block text-xl" id="paymentTotal"></strong></div>
                <div>New due (preview)<strong class="block text-xl" id="paymentDue"></strong></div>
                <div>Payment method<strong class="block" id="paymentMethod"></strong></div>
            </div>
        </div>
        <div id="noteSection" class="hidden space-y-3">
            <p class="text-sm text-gray-500">These are prescription-level notes. Patient-specific diagnosis/precautions remain in each patient tab.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach(['diagnosis' => 'General diagnosis', 'lab_workup' => 'Lab workup', 'precautions' => 'General precautions', 'physiotherapy' => 'Physiotherapy', 'notes' => 'Prescription notes', 'next_visit' => 'General follow-up'] as $key => $label)
                <label class="block text-sm font-semibold text-gray-600">{{ $label }}<textarea data-header="{{ $key }}" rows="3" maxlength="{{ $key === 'next_visit' ? 1000 : 10000 }}" class="w-full border rounded-lg p-2 mt-1 font-normal"></textarea></label>
                @endforeach
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 flex-wrap">
            <a href="{{ route('prescriptions.show', $prescription) }}" class="border px-5 py-2.5 rounded-lg text-gray-600">Cancel</a>
            <button type="button" id="saveBtn" onclick="savePrescription()" disabled class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-8 py-2.5 rounded-lg font-semibold text-lg"><i class="fas fa-check mr-1"></i> Update Prescription (Print)</button>
        </div>
    </div>

    {{-- Right: selected patient details and all-patient fee summary --}}
    <div class="w-full lg:w-80 flex-shrink-0">
        <div id="patientPanel" class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-gray-400 py-10">
                <i class="fas fa-user-injured text-5xl mb-3"></i>
                <p class="font-semibold text-gray-600">No patient selected</p>
                <button onclick="openPatientPanel()" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-users mr-1"></i>Select Patient</button>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 mt-4">
            <h3 class="font-bold text-gray-800 mb-3">Fee Summary <span class="text-xs font-normal text-gray-400">All patients</span></h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-600">Medicine Cost</span><span id="sumMedicine" class="font-semibold">0.00</span></div>
                <div class="flex justify-between text-red-600 font-semibold"><span><i class="fas fa-x-ray mr-1"></i>Radiology (extra)</span><span id="sumRadiology">0.00</span></div>
                <div class="flex justify-between items-center"><span class="text-gray-600">Doctor Fee <small class="block text-gray-400">Selected patient</small></span><input type="number" id="docFee" value="0" min="0" max="1000000" step="0.01" class="w-24 border rounded px-2 py-1 text-right" oninput="saveActivePatientState();updateTotals()"></div>
                <div class="flex justify-between items-center"><span class="text-gray-600">Discount <small class="block text-gray-400">Selected patient</small></span><input type="number" id="disc" value="0" min="0" max="1000000" step="0.01" class="w-24 border rounded px-2 py-1 text-right" oninput="saveActivePatientState();updateTotals()"></div>
                <div class="flex justify-between"><span class="text-gray-600">All doctor fees</span><span id="sumDoctors">0.00</span></div>
                <div class="flex justify-between"><span class="text-gray-600">All patient discounts</span><span id="sumDiscount">0.00</span></div>
                <div class="flex justify-between items-center"><span class="text-gray-600">Tax (whole Rx)</span><input id="taxInput" type="number" min="0" max="1000000" step="0.01" class="w-24 border rounded px-2 py-1 text-right"></div>
                <div class="border-t pt-2 flex justify-between text-lg font-bold"><span>Total Fee</span><span id="sumTotal" class="text-green-600">0.00</span></div>
            </div>
            <p class="text-xs text-gray-400 mt-2"><i class="fas fa-info-circle"></i> Totals are a preview. The server validates and saves totals and stock together.</p>
        </div>
    </div>
</div>

{{-- ====== SIDEBAR PATIENT PANEL ====== --}}
<div id="backdrop" class="backdrop fixed inset-0 bg-black/50 z-40" onclick="closePatientPanel()"></div>
<div id="sidePanel" class="side-panel fixed top-0 right-0 h-full w-full sm:w-[440px] bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-xl font-bold text-gray-800" id="patientListTitle">Patient List</h2>
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
        <p class="text-xs text-gray-400 mb-2">Select an existing patient or create a new patient record.</p>
        <button type="button" onclick="showCreatePatient()" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold">Create Patient</button>
    </div>
</div>

{{-- ====== CREATE PATIENT SUB-PANEL ====== --}}
<div id="createPanel" class="hidden fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white shadow-2xl z-50 flex flex-col">
    <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-xl font-bold text-gray-800">Patient Create</h2>
        <button onclick="closeCreatePatient()" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
    </div>
    <form id="createPatientForm" class="p-5 space-y-3 flex-1 overflow-y-auto" onsubmit="submitPatient(event)">
        <p class="text-xs text-amber-700 bg-amber-50 p-2 rounded">This creates a patient record immediately. Prescription changes are saved only with Update Prescription.</p>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Name</label><input type="text" name="name" required class="w-full border rounded-lg px-3 py-2" placeholder="Name"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Email</label><input type="email" name="email" class="w-full border rounded-lg px-3 py-2" placeholder="Email"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Phone</label><input type="text" name="phone" class="w-full border rounded-lg px-3 py-2" placeholder="Phone"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Age (Year)</label><input type="number" name="age" min="0" max="150" class="w-full border rounded-lg px-3 py-2" placeholder="Age (Year)"></div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Gender</label>
            <select name="gender" class="w-full border rounded-lg px-3 py-2"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
        </div>
        <div><label class="block font-semibold text-gray-700 mb-1 text-sm">Address</label><textarea name="address" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea></div>
        <button type="submit" class="w-full bg-blue-900 text-white py-3 rounded-lg font-bold text-lg">Save</button>
        <button type="button" onclick="closeCreatePatient()" class="w-full bg-yellow-400 text-gray-900 py-3 rounded-lg font-bold text-lg"><i class="fas fa-angle-double-left"></i> Back</button>
    </form>
</div>
</fieldset>
<div id="rxPopupOverlay" hidden class="fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="popupTitle"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/clinicms-print-server.js') }}"></script>
<script>
(() => {
'use strict';
const PRODUCTS = @json($products);
const PATIENTS = @json($allPatients);
const RADIOLOGY = @json($radiologyTests);
const DOCTORS = @json($doctors);
const RX_NUMBER = @json($prescription->prescription_number);
const RX_ID = @json($prescription->id);
const URLS = {
    update: @json(route('prescriptions.update', $prescription)),
    edit: @json(route('prescriptions.edit', $prescription)),
    index: @json(route('prescriptions.index')),
    show: @json(route('prescriptions.show', $prescription)),
    pdf: @json(route('prescriptions.print', $prescription)),
    patients: @json(route('patients.store')),
    template: @json(route('treatments.show.data', ['treatment' => '__ID__']))
};
const CSRF = @json(csrf_token());
let state = @json($seed);
let legacyIds = new Set(@json($legacyIds).map(Number));
let activePatientIdx=0, drugTypeFilter='', replacingItem=null, pickerMode='add';
let dirty=false, saving=false, pendingOperations=0, printMayHaveChanged=false, dropdownIdx=-1, lastFocus=null;
const $ = id => document.getElementById(id);
const esc = value => String(value ?? '').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const cents = n => Math.round(Number(n || 0)*100);
const fmt = centsValue => (centsValue/100).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
const qtyFormat = n => String(Number(n || 0));
const getProduct = id => PRODUCTS.find(p=>String(p.id)===String(id));
const current = () => state.patients[activePatientIdx];
const locked = item => legacyIds.has(Number(item.id));
const frequency = {OD:1,BD:2,BID:2,TDS:3,QID:4};
const lineTotal = i => Math.round(cents(i.quantity)*cents(i.unit_price)/100)-cents(i.discount);
function prepareState(){
    if(!Array.isArray(state.patients)) state.patients=[];
    state.patients=state.patients.filter(p=>p && typeof p==='object');
    state.patients.forEach(p=>{p.items=Array.isArray(p.items)?p.items:[];p.radiologies=Array.isArray(p.radiologies)?p.radiologies:[];});
}
function changed(){dirty=true;$('saveState').textContent='Unsaved changes';}
function refreshSaveButton(){ $('saveBtn').disabled=saving || pendingOperations>0; }
function errors(messages){
    const box=$('editErrors');box.textContent='';
    const title=document.createElement('strong');title.textContent='Please review';box.appendChild(title);
    messages.forEach(message=>{const line=document.createElement('p');line.className='rx-error-line text-sm mt-1';line.textContent=String(message);box.appendChild(line);});
    box.classList.remove('hidden');box.focus();
}
function showEditorTab(name){
    ['prescription','payment','note'].forEach(n=>{
        $(n+'Section').classList.toggle('hidden',n!==name);
        const tab=document.querySelector(`[data-tab="${n}"]`);
        tab.classList.toggle('text-blue-600',n===name);tab.classList.toggle('border-blue-600',n===name);
        tab.classList.toggle('border-transparent',n!==name);tab.setAttribute('aria-selected',n===name?'true':'false');
    });
}
function patientInfo(pp){
    const p=PATIENTS.find(p=>String(p.id)===String(pp.patient_id)) || {};
    return {...p,name:pp.patient_name ?? p.name ?? 'Patient',age:pp.patient_age ?? p.age,
        phone:pp.patient_phone ?? p.phone,patient_code:pp.patient_code ?? p.patient_code};
}
function hydrateHeader(){
    $('prescriptionDate').value=String(state.prescription_date||'').slice(0,10);
    $('taxInput').value=state.tax ?? 0;
    document.querySelectorAll('[data-header]').forEach(el=>el.value=state[el.dataset.header] ?? '');
}
function saveActivePatientState(){
    const p=current();if(!p)return;
    p.doctor_id=$('doctorSelect').value || null;p.doctor_fee=$('docFee').value;p.discount=$('disc').value;
    p.diagnosis=$('diagInput').value;p.precautions=$('precInput').value;p.next_visit=$('nextVisitInput').value;
    // Medicine and radiology objects are updated in-place by field listeners.
    // Never reconstruct them from DOM rows: persisted IDs and notes must survive tab changes.
}
function renderDiagnosis(){
    const p=current();
    $('doctorSelect').value=p?.doctor_id ?? '';$('docFee').value=p?.doctor_fee ?? 0;$('disc').value=p?.discount ?? 0;
    $('diagInput').value=p?.diagnosis ?? '';$('precInput').value=p?.precautions ?? '';$('nextVisitInput').value=p?.next_visit ?? '';
}
function renderPatientTabs(){
    $('patientTabs').innerHTML=state.patients.map((p,i)=>{
        const info=patientInfo(p), blocked=p.items.some(locked);
        return `<div class="patient-tab flex items-center gap-2 px-3 py-2 border-2 rounded-lg text-sm font-semibold ${i===activePatientIdx?'active':''}">
        <button type="button" role="tab" aria-selected="${i===activePatientIdx}" onclick="switchPatient(${i})" class="flex items-center gap-2"><i class="fas fa-user"></i><span class="max-w-[140px] truncate">${esc(info.name)}</span></button>
        ${state.patients.length>1?`<button type="button" onclick="removePatient(${i})" ${blocked?'disabled':''} aria-label="Remove patient" title="${blocked?'Contains historical stock items':'Remove patient'}" class="ml-1 ${blocked?'opacity-30':'hover:text-red-400'}">&times;</button>`:''}</div>`;
    }).join('');
}
function renderPatientPanel(){
    const pp=current();if(!pp){$('patientPanel').innerHTML='<p class="text-gray-400 py-6">Select a patient to begin.</p>';return;}
    const p=patientInfo(pp);
    $('patientPanel').innerHTML=`<div class="flex justify-between items-center mb-3"><h3 class="font-bold text-gray-800">Patient Details</h3><button type="button" onclick="openPatientPanel('replace')" class="text-blue-600 text-sm hover:underline"><i class="fas fa-exchange-alt"></i> Change</button></div>
    <div class="text-center mb-4"><div class="w-24 h-24 mx-auto bg-gray-200 rounded-full flex items-center justify-center text-gray-400 mb-2"><i class="fas fa-user text-5xl"></i></div>
    <h4 class="font-bold text-lg text-gray-800">${esc(p.name)}</h4><p class="text-sm text-gray-500">${esc(p.age ?? '-')} years old, ${esc(p.gender || '-')}</p><p class="text-xs text-gray-400 mt-1">${esc(p.patient_code || '-')}</p></div>
    <div class="text-sm space-y-1 border-t pt-3 text-left"><p><i class="fas fa-phone w-5 text-gray-400"></i> ${esc(p.phone || '-')}</p><p class="break-words"><i class="fas fa-envelope w-5 text-gray-400"></i> ${esc(p.email || '-')}</p></div>
    <div class="mt-4 p-3 bg-blue-50 rounded-lg text-center"><div class="text-xs text-blue-700 font-semibold mb-1">Prescription ID</div><div class="font-mono font-bold text-blue-900 text-lg break-all">${esc(RX_NUMBER)}</div><i class="fas fa-prescription text-3xl text-blue-200 my-2" aria-hidden="true"></i><p class="text-xs text-blue-700">Existing ID — unchanged on update</p></div>`;
}
function medicineField(i,key,value,cls,extra=''){
    return `<input data-item="${i}" data-field="${key}" value="${esc(value)}" class="${cls}" ${extra}>`;
}
function renderMedicineRows(){
    const p=current();
    $('medicineRows').innerHTML=(p?.items || []).map((item,i)=>{
        const product=getProduct(item.product_id), isLocked=locked(item), stock=Number(product?.stock_quantity ?? 0);
        const timingOpts=['OD','BD','BID','TDS','QID'].map(t=>`<option value="${t}" ${item.timing===t?'selected':''}>${t} (${frequency[t]}x)</option>`).join('');
        const meals=['','before','after'];if(item.meal_relation && !meals.includes(item.meal_relation))meals.push(item.meal_relation);
        const mealOpts=meals.map(m=>`<option value="${esc(m)}" ${(item.meal_relation||'')===m?'selected':''}>${esc(m?m[0].toUpperCase()+m.slice(1):'-')}</option>`).join('');
        return `<tr class="med-row" data-row="${i}">
        <td class="p-2 drug-label">${medicineField(i,'drug_name',item.drug_name,'w-full font-semibold text-gray-800 border rounded px-2 py-1','type="text" maxlength="255" required aria-label="Printed medicine name" title="Printed label only; use Change to select a different product"')}
            <div class="text-[10px] text-gray-400 mt-1">Product: ${esc(product?.name || 'Not selected')}</div>
            <button type="button" onclick="replaceMedicine(${i})" class="text-xs text-blue-600 hover:underline mt-1 disabled:opacity-40" ${isLocked?'disabled':''}>${isLocked?'<i class="fas fa-lock"></i> Historical stock':'Change product'}</button></td>
        <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold ${stock<=0?'bg-red-100 text-red-700':stock<=10?'bg-amber-100 text-amber-700':'bg-green-100 text-green-700'}">${qtyFormat(stock)}</span></td>
        <td class="p-2 text-gray-600 text-xs">${esc(item.form_type ?? product?.form_type ?? '-')}</td>
        <td class="p-2">${medicineField(i,'strength',item.strength,'border rounded px-2 py-1 w-20 text-xs font-semibold text-purple-700','type="text" maxlength="255" aria-label="Strength"')}<div class="text-[10px] text-gray-400 mt-1">Base: ${esc(product?.strength || '-')}</div></td>
        <td class="p-2"><select data-item="${i}" data-field="meal_relation" aria-label="Meal" class="border rounded px-1 py-1 text-xs">${mealOpts}</select></td>
        <td class="p-2"><select data-item="${i}" data-field="timing" aria-label="Timing" class="border rounded px-2 py-1">${timingOpts}</select></td>
        <td class="p-2">${medicineField(i,'duration_days',item.duration_days,'border rounded px-2 py-1 w-16','type="number" required min="1" max="3650" step="1" aria-label="Days"')}</td>
        <td class="p-2">${medicineField(i,'quantity',item.quantity,'border rounded px-2 py-1 w-20 font-bold text-blue-600',`type="number" required min="0.01" max="10000" step="0.01" aria-label="Quantity in stock units" ${isLocked?'disabled':''}`)}<span class="text-[10px] text-gray-400">${isLocked?'Locked':'Stock units'}</span></td>
        <td class="p-2">${medicineField(i,'unit_price',item.unit_price,'border rounded px-2 py-1 w-20','type="number" required min="0" max="1000000" step="0.01" aria-label="Unit price"')}</td>
        <td class="p-2">${medicineField(i,'discount',item.discount ?? 0,'border rounded px-2 py-1 w-20','type="number" required min="0" max="1000000" step="0.01" aria-label="Medicine discount"')}</td>
        <td class="p-2">${medicineField(i,'instruction',item.instruction,'border rounded px-2 py-1 w-32 text-xs mb-1','type="text" maxlength="255" placeholder="Instruction" aria-label="Instruction"')}<textarea data-item="${i}" data-field="instruction_note" aria-label="Instruction note" maxlength="10000" rows="2" placeholder="Note..." class="border rounded px-2 py-1 w-32 text-xs">${esc(item.instruction_note)}</textarea></td>
        <td class="p-2 font-bold text-gray-800 whitespace-nowrap" data-line-total="${i}">${fmt(lineTotal(item))}</td>
        <td class="p-2"><button type="button" onclick="removeMedicineRow(${i})" ${isLocked?'disabled':''} class="text-red-500 hover:text-red-700 disabled:opacity-30" aria-label="Remove medicine"><i class="fas fa-times"></i></button></td></tr>`;
    }).join('') || '<tr><td colspan="13" class="text-center py-8 text-gray-400">Search for a medicine above to add it.</td></tr>';
}
function renderRadiology(){
    const p=current();
    $('radTags').innerHTML=(p?.radiologies||[]).map((r,i)=>`<div class="flex flex-wrap gap-2 items-center bg-white rounded-lg p-2 border border-blue-100">
        <span class="text-blue-600"><i class="fas fa-x-ray"></i></span><input data-rad="${i}" data-field="test_name" class="border rounded px-2 py-1 text-sm flex-1 min-w-[160px]" value="${esc(r.test_name)}" maxlength="255" placeholder="Test name" aria-label="Test name">
        <span class="text-xs text-blue-600">Rs</span><input data-rad="${i}" data-field="price" class="w-24 border rounded px-2 py-1 text-sm" type="number" min="0" max="1000000" step="0.01" value="${esc(r.price)}" aria-label="Test price">
        <textarea data-rad="${i}" data-field="notes" rows="1" maxlength="10000" class="border rounded px-2 py-1 text-xs flex-1" placeholder="Test notes" aria-label="Test notes">${esc(r.notes)}</textarea><button type="button" onclick="removeRadiology(${i})" class="text-red-500 px-2" aria-label="Remove test">&times;</button></div>`).join('');
    $('radTotalLine').innerHTML=`Radiology total: <strong>Rs ${fmt((p?.radiologies||[]).reduce((s,r)=>s+cents(r.price),0))}</strong>`;
}
function updateTotals(){
    let medicine=0,radiology=0,doctor=0,discount=0;
    state.patients.forEach(p=>{medicine+=p.items.reduce((s,i)=>s+lineTotal(i),0);radiology+=p.radiologies.reduce((s,r)=>s+cents(r.price),0);doctor+=cents(p.doctor_fee);discount+=cents(p.discount);});
    const total=medicine+radiology+doctor-discount+cents(state.tax),paid=cents(state.paid_amount);
    $('sumMedicine').textContent=fmt(medicine);$('sumRadiology').textContent=fmt(radiology);$('sumDoctors').textContent=fmt(doctor);$('sumDiscount').textContent=fmt(discount);$('sumTotal').textContent=fmt(total);
    $('paymentPaid').textContent='Rs '+fmt(paid);$('paymentTotal').textContent='Rs '+fmt(total);$('paymentDue').textContent='Rs '+fmt(Math.max(0,total-paid));$('paymentMethod').textContent=state.payment_method || '-';
}
function renderAll(){
    replacingItem=null;$('replaceNotice').classList.add('hidden');
    renderPatientTabs();renderPatientPanel();renderDiagnosis();renderMedicineRows();renderRadiology();updateTotals();
}
function switchPatient(i){if(saving)return;saveActivePatientState();activePatientIdx=i;closeDrugDropdown();renderAll();}
function removePatient(i){
    if(saving || state.patients.length<=1)return;
    saveActivePatientState();if(state.patients[i].items.some(locked)){alert('This patient contains historical stock items and cannot be removed.');return;}
    if(!confirm('Remove this patient and their medicines/tests when Update is pressed?'))return;
    const previous=current();state.patients.splice(i,1);activePatientIdx=Math.max(0,state.patients.indexOf(previous));changed();renderAll();
}
function openPatientPanel(mode='add'){
    if(saving)return;saveActivePatientState();pickerMode=mode;lastFocus=document.activeElement;
    $('patientListTitle').textContent=mode==='replace'?'Change Selected Patient':'Add Patient to Prescription';
    $('sidePanel').classList.add('open');$('backdrop').classList.add('show');$('pSearch').value='';renderPatientList();$('pSearch').focus();
}
function closePatientPanel(){ $('sidePanel').classList.remove('open');$('backdrop').classList.remove('show');if(lastFocus?.isConnected)lastFocus.focus(); }
function renderPatientList(){
    const q=$('pSearch').value.trim().toLowerCase();
    $('pList').innerHTML=PATIENTS.filter(p=>[p.name,p.phone,p.patient_code].some(v=>String(v||'').toLowerCase().includes(q))).map(p=>`<button type="button" onclick="selectPatient(${Number(p.id)})" class="w-full text-left flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 border-b"><div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 flex-shrink-0"><i class="fas fa-user text-2xl"></i></div><div class="min-w-0"><div class="font-bold text-gray-800 truncate">${esc(p.name)}</div><div class="text-sm text-gray-500">${esc(p.phone||p.patient_code||'')}</div></div></button>`).join('') || '<p class="text-gray-400 text-center py-6">No patients found</p>';
}
function selectPatient(id){
    if(saving)return;saveActivePatientState();const p=PATIENTS.find(p=>String(p.id)===String(id));if(!p)return;
    const duplicate=state.patients.findIndex(pp=>String(pp.patient_id)===String(id));
    if(pickerMode==='replace' && current()){
        if(duplicate>=0 && duplicate!==activePatientIdx){alert('This patient is already included. Select their patient tab instead.');return;}
        if(String(current().patient_id)!==String(id)){
            Object.assign(current(),{patient_id:p.id,patient_name:p.name,patient_age:p.age,patient_phone:p.phone,patient_code:p.patient_code});changed();
        }
    }else if(duplicate>=0){activePatientIdx=duplicate;}
    else{
        if(state.patients.length>=50){alert('Maximum 50 patients.');return;}
        state.patients.push({id:null,patient_id:p.id,patient_name:p.name,patient_age:p.age,patient_phone:p.phone,patient_code:p.patient_code,doctor_id:null,doctor_fee:0,discount:0,diagnosis:'',precautions:'',next_visit:'',items:[],radiologies:[]});
        activePatientIdx=state.patients.length-1;changed();
    }
    closePatientPanel();showEditorTab('prescription');renderAll();
}
function showCreatePatient(){lastFocus=document.activeElement;$('createPanel').classList.remove('hidden');$('createPatientForm').elements.name.focus();}
function closeCreatePatient(){ $('createPanel').classList.add('hidden');$('pSearch').focus(); }
async function jsonResponse(res){
    if(res.redirected || !(res.headers.get('content-type')||'').includes('application/json'))throw new Error('Session expired or unexpected server response. Reload the saved prescription before retrying.');
    return await res.json();
}
async function submitPatient(event){
    event.preventDefault();if(saving || pendingOperations)return;
    const form=event.target;if(!form.reportValidity())return;
    const data=Object.fromEntries(new FormData(form));if(data.age==='')delete data.age;
    pendingOperations++;refreshSaveButton();const submit=form.querySelector('[type="submit"]');submit.disabled=true;
    try{
        const res=await fetch(URLS.patients,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify(data)});
        const json=await jsonResponse(res);
        if(!res.ok || !json.success || !json.patient){throw new Error(Object.values(json.errors||{}).flat().join('\n') || json.message || 'Could not create patient.');}
        PATIENTS.unshift(json.patient);form.reset();closeCreatePatient();pickerMode='add';selectPatient(json.patient.id);
    }catch(e){alert(e.message);}finally{pendingOperations--;submit.disabled=false;refreshSaveButton();}
}
function closeDrugDropdown(){ $('drugDropdown').classList.add('hidden');$('drugSearch').setAttribute('aria-expanded','false');dropdownIdx=-1; }
function onDrugSearch(){
    const q=$('drugSearch').value.trim().toLowerCase();const dd=$('drugDropdown');
    if(q.length<2 && !drugTypeFilter){closeDrugDropdown();return;}
    const rows=PRODUCTS.filter(p=>Number(p.is_active)===1 && !p.deleted_at && String(p.name).toLowerCase().includes(q) && (!drugTypeFilter || String(p.drug_type_id)===drugTypeFilter)).slice(0,40);
    dd.innerHTML=rows.map(p=>`<button type="button" role="option" onclick="chooseDrug(${Number(p.id)})" class="drug-option w-full text-left px-3 py-2 border-b flex justify-between gap-3"><span><strong>${esc(p.name)}</strong><small class="block text-gray-400">${esc(p.form_type||'')} · ${esc(p.strength||'')}</small></span><span class="text-right text-xs text-gray-500">Rs ${fmt(cents(p.selling_price))}<br>Stock: ${qtyFormat(p.stock_quantity)}</span></button>`).join('') || '<p class="p-4 text-gray-400 text-sm">No matching active medicines.</p>';
    dd.classList.remove('hidden');$('drugSearch').setAttribute('aria-expanded','true');dropdownIdx=-1;
}
function filterDrugType(id){drugTypeFilter=String(id||'');document.querySelectorAll('.drug-chip').forEach(el=>el.classList.toggle('active',el.dataset.type===drugTypeFilter));onDrugSearch();}
function quickDrugType(id){$('drugTypeFilter').value=id;filterDrugType(id);$('drugSearch').focus();}
function newMedicine(product){return {id:null,product_id:product.id,drug_name:product.name,form_type:product.form_type,strength:product.strength||'',timing:'OD',duration_days:1,quantity:'',unit_price:product.selling_price,discount:0,meal_relation:'',instruction:'',instruction_note:''};}
function chooseDrug(id){
    if(saving)return;if(!current()){openPatientPanel();return;}saveActivePatientState();
    const product=getProduct(id);if(!product || !Number(product.is_active) || product.deleted_at)return;
    if(replacingItem!==null){
        const item=current().items[replacingItem];if(!item || locked(item))return;
        Object.assign(item,{product_id:product.id,drug_name:product.name,form_type:product.form_type,strength:product.strength||'',unit_price:product.selling_price});
    }else{
        if(current().items.length>=100){alert('Maximum 100 medicines per patient.');return;}
        current().items.unshift(newMedicine(product));
    }
    replacingItem=null;$('replaceNotice').classList.add('hidden');$('drugSearch').value='';closeDrugDropdown();changed();renderMedicineRows();updateTotals();
}
function addEmptyMedicineRow(){
    if(!current()){openPatientPanel();return;}
    // Do not add an unidentified row that could silently disappear on Save.
    $('drugSearch').focus();if(!$('drugSearch').value && !drugTypeFilter){$('drugSearch').placeholder='Type at least two characters to select a medicine…';}onDrugSearch();
}
function replaceMedicine(i){
    if(saving || locked(current().items[i]))return;replacingItem=i;
    $('replaceNotice').textContent='Select the replacement product for '+(current().items[i].drug_name||'this medicine')+'. Quantity stays unchanged; review it before saving.';
    $('replaceNotice').classList.remove('hidden');$('drugSearch').value='';$('drugSearch').focus();
}
function removeMedicineRow(i){
    if(saving || locked(current().items[i]))return;
    if(!confirm('Remove this medicine when Update is pressed?'))return;
    current().items.splice(i,1);replacingItem=null;$('replaceNotice').classList.add('hidden');changed();renderMedicineRows();updateTotals();renderPatientTabs();
}
function addRadiology(){
    if(saving || !current())return;const test=RADIOLOGY.find(r=>String(r.id)===$('radSelect').value);
    if(!test){alert('Select a test first.');return;}if(current().radiologies.length>=100)return;
    current().radiologies.push({id:null,radiology_test_id:test.id,test_name:test.name,price:test.price,notes:''});$('radSelect').value='';changed();renderRadiology();updateTotals();
}
function addCustomRadiology(){if(saving || !current() || current().radiologies.length>=100)return;current().radiologies.push({id:null,radiology_test_id:null,test_name:'',price:0,notes:''});changed();renderRadiology();updateTotals();}
function removeRadiology(i){if(saving)return;current().radiologies.splice(i,1);changed();renderRadiology();updateTotals();}
async function applyTemplate(id){
    if(!id || saving || pendingOperations)return;const target=current();if(!target){alert('Select a patient first.');return;}
    saveActivePatientState();pendingOperations++;refreshSaveButton();
    try{
        const res=await fetch(URLS.template.replace('__ID__',encodeURIComponent(id)),{credentials:'same-origin',headers:{Accept:'application/json'}});
        const tpl=await jsonResponse(res);if(!res.ok || !Array.isArray(tpl.items))throw new Error('Could not load the template.');
        if(!state.patients.includes(target))throw new Error('The target patient was removed. No template items were added.');
        let added=0;
        for(const it of tpl.items){
            const p=getProduct(it.product_id);if(!p || !Number(p.is_active) || p.deleted_at || target.items.length>=100)continue;
            const row=newMedicine(p);Object.assign(row,{timing:it.timing || 'OD',duration_days:it.duration_days ?? 1,meal_relation:it.meal_relation ?? '',instruction_note:it.instruction ?? ''});
            // No inferred stock-unit/dose conversion. Review quantity explicitly.
            row.quantity=it.quantity ?? '';target.items.push(row);added++;
        }
        if(added)changed();if(current()===target)renderMedicineRows();updateTotals();alert(`${added} medicine(s) added. Review quantities before saving. Existing rows were preserved.`);
    }catch(e){alert(e.message);}finally{$('rtSelect').value='';pendingOperations--;refreshSaveButton();}
}
async function savePrescription(){
    if(saving || pendingOperations)return;saveActivePatientState();state.prescription_date=$('prescriptionDate').value;state.tax=$('taxInput').value;
    if(!state.patients.length){errors(['Add at least one patient.']);return;}
    for(let pi=0;pi<state.patients.length;pi++){
        const p=state.patients[pi];
        if(!p.items.length || p.items.some(i=>!i.product_id || !Number.isFinite(Number(i.quantity)) || Number(i.quantity)<=0)){
            activePatientIdx=pi;renderAll();showEditorTab('prescription');errors(['Each patient needs at least one medicine and a positive, reviewed quantity for every medicine.']);return;
        }
    }
    if(!$('prescriptionDate').reportValidity())return;
    saving=true;$('editorFieldset').disabled=true;refreshSaveButton();$('saveBtn').textContent='Updating…';$('editErrors').classList.add('hidden');
    const activeId=current()?.id, activePatientId=current()?.patient_id;
    try{
        const res=await fetch(URLS.update,{method:'PUT',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({payload:JSON.stringify(state)})});
        const json=await jsonResponse(res);
        if(!res.ok || !json.success){
            const entries=Object.entries(json.errors||{}), patientError=entries.find(([k])=>/^patients\.\d+/.test(k));
            if(patientError){activePatientIdx=Number(patientError[0].split('.')[1]);renderAll();showEditorTab('prescription');}
            errors(entries.length?entries.flatMap(([,v])=>Array.isArray(v)?v:[v]):[json.message||'Update failed. No success response was received.']);return;
        }
        if(!json.seed || !Array.isArray(json.seed.patients) || !json.seed.revision)throw new Error('The server did not return refreshed editor data. Reload saved data before retrying.');
        state=json.seed;prepareState();legacyIds=new Set((json.legacyIds||[]).map(Number));
        PRODUCTS.forEach(p=>p.stock_quantity=Number((json.stockQuantities||{})[p.id]||0));
        activePatientIdx=state.patients.findIndex(p=>activeId ? String(p.id)===String(activeId) : String(p.patient_id)===String(activePatientId));if(activePatientIdx<0)activePatientIdx=0;
        dirty=false;hydrateHeader();renderAll();$('legacyWarning').classList.toggle('hidden',legacyIds.size===0);$('saveState').textContent='Changes saved';showUpdatedPopup();
    }catch(e){errors([e.message,'If the connection failed after sending, the update may already have committed. Reload saved data and check before retrying.']);}
    finally{saving=false;$('editorFieldset').disabled=false;$('saveBtn').innerHTML='<i class="fas fa-check mr-1"></i> Update Prescription (Print)';refreshSaveButton();}
}
function showUpdatedPopup(){
    printMayHaveChanged=false;const overlay=$('rxPopupOverlay');lastFocus=$('saveBtn');
    const preview=state.patients.map((p,n)=>`<section class="border rounded mb-3 bg-gray-50"><div class="bg-blue-100 px-3 py-2 flex justify-between text-sm gap-2"><strong>Patient ${n+1}: ${esc(p.patient_name||patientInfo(p).name)}</strong><span>Doctor fee: Rs ${fmt(cents(p.doctor_fee))}</span></div>
    <table class="w-full text-xs"><thead><tr class="bg-white"><th class="p-2 text-left">Medicine</th><th class="p-2 text-right">Qty</th></tr></thead><tbody>${p.items.map(i=>`<tr class="border-b"><td class="p-2"><strong>${esc(i.drug_name)}</strong> ${esc(i.strength||'')}<span class="block text-gray-500">${esc(i.timing)} × ${esc(i.duration_days)} days · ${esc(i.instruction||'')} ${esc(i.instruction_note||'')}</span></td><td class="p-2 text-right font-bold">${esc(i.quantity)}</td></tr>`).join('')}</tbody></table>
    ${p.radiologies.map(r=>`<p class="text-xs text-amber-700 px-3 py-1">[R] ${esc(r.test_name)} · Rs ${fmt(cents(r.price))}</p>`).join('')}</section>`).join('');
    overlay.innerHTML=`<div class="bg-white rounded-2xl shadow-2xl p-6 max-w-3xl w-full my-8"><div class="text-center mb-4"><div class="w-14 h-14 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-3"><i class="fas fa-check text-green-600 text-2xl"></i></div><h2 id="popupTitle" class="text-xl font-bold text-gray-800">Prescription Updated!</h2><p class="text-xs text-gray-500 mt-1">The existing prescription was updated. No new Rx was created.</p></div>
    <div class="bg-blue-50 rounded-lg p-4 mb-4 text-center"><div class="text-xs text-blue-700 font-semibold">Prescription ID</div><div class="font-mono font-bold text-blue-900 text-2xl my-2">${esc(RX_NUMBER)}</div></div>
    <h3 class="font-bold text-gray-700 mb-2">Saved Prescription Preview</h3><div class="max-h-80 overflow-y-auto border rounded p-2 mb-3">${preview}</div>
    <p id="printMessage" class="text-xs text-gray-500 mb-3">Printing uses the existing clinic print layout. After a print attempt, Continue Editing reloads the latest revision.</p>
    <div class="flex justify-end gap-3 mb-3"><button type="button" id="popupSetup" class="text-xs text-blue-700 underline">Print Server Setup / Pair</button><a id="popupPdf" href="${esc(URLS.pdf)}" target="_blank" rel="noopener" class="text-xs text-blue-700 underline">Open PDF</a></div>
    <div class="flex gap-2 flex-wrap"><button type="button" id="popupContinue" class="flex-1 bg-yellow-500 text-white py-3 px-3 rounded-lg font-bold">Continue Editing</button><button type="button" id="popupPrint" class="flex-1 bg-green-600 text-white py-3 px-3 rounded-lg font-bold">Print</button><a href="${esc(URLS.index)}" class="flex-1 bg-blue-600 text-white py-3 px-3 rounded-lg font-bold text-center">All Prescriptions</a></div></div>`;
    overlay.hidden=false;$('popupContinue').focus();
    $('popupContinue').onclick=closeUpdatedPopup;
    $('popupPdf').onclick=()=>{printMayHaveChanged=true;};
    $('popupPrint').onclick=async function(){
        const printer=typeof ClinicMSPrintServer==='undefined'?null:ClinicMSPrintServer;
        if(!printer?.printExisting){$('printMessage').textContent='Prescription is saved. Print server is unavailable; use Open PDF or check the existing print-server script.';return;}
        printMayHaveChanged=true;this.disabled=true;$('popupContinue').disabled=true;
        try{await printer.printExisting(RX_ID,RX_NUMBER,this);}catch(e){$('printMessage').textContent='Prescription is saved, but printing failed: '+e.message;}
        finally{this.disabled=false;$('popupContinue').disabled=false;}
    };
    $('popupSetup').onclick=async()=>{
        try{const printer=typeof ClinicMSPrintServer==='undefined'?null:ClinicMSPrintServer;if(!printer?.openSetup)throw new Error('Print server script is unavailable.');await printer.openSetup();}
        catch(e){$('printMessage').textContent=e.message;}
    };
}
function closeUpdatedPopup(){
    if($('popupContinue')?.disabled)return;
    if(printMayHaveChanged){window.location.assign(URLS.edit);return;}
    $('rxPopupOverlay').hidden=true;$('saveBtn').focus();
}
// In-place field updates preserve every original item/radiology ID and unexposed field.
function editMedicine(event){
    const el=event.target;if(!el.hasAttribute('data-item') || saving)return;
    const item=current()?.items[Number(el.dataset.item)];if(!item)return;
    const key=el.dataset.field;if(locked(item) && ['product_id','quantity'].includes(key))return;
    item[key]=el.value;changed();const total=document.querySelector(`[data-line-total="${el.dataset.item}"]`);if(total)total.textContent=fmt(lineTotal(item));updateTotals();
}
$('medicineRows').addEventListener('input',editMedicine);$('medicineRows').addEventListener('change',editMedicine);
$('radTags').addEventListener('input',event=>{
    const el=event.target;if(!el.hasAttribute('data-rad') || saving)return;const r=current().radiologies[Number(el.dataset.rad)];r[el.dataset.field]=el.value;changed();updateTotals();
    $('radTotalLine').innerHTML=`Radiology total: <strong>Rs ${fmt(current().radiologies.reduce((s,r)=>s+cents(r.price),0))}</strong>`;
});
['diagInput','precInput','nextVisitInput','docFee','disc','doctorSelect'].forEach(id=>{
    $(id).addEventListener('input',()=>{if(saving)return;saveActivePatientState();changed();updateTotals();});
    $(id).addEventListener('change',()=>{if(saving)return;saveActivePatientState();changed();updateTotals();});
});
$('prescriptionDate').addEventListener('input',()=>{state.prescription_date=$('prescriptionDate').value;changed();});
$('taxInput').addEventListener('input',()=>{state.tax=$('taxInput').value;changed();updateTotals();});
document.querySelectorAll('[data-header]').forEach(el=>el.addEventListener('input',()=>{state[el.dataset.header]=el.value;changed();}));
document.addEventListener('click',e=>{if(!$('drugSearchWrap').contains(e.target))closeDrugDropdown();});
$('drugSearch').addEventListener('keydown',event=>{
    const options=[...$('drugDropdown').querySelectorAll('[role="option"]')];if($('drugDropdown').classList.contains('hidden'))return;
    if(event.key==='Escape'){closeDrugDropdown();return;}
    if(['ArrowDown','ArrowUp'].includes(event.key)){
        event.preventDefault();if(!options.length)return;dropdownIdx=(dropdownIdx+(event.key==='ArrowDown'?1:-1)+options.length)%options.length;options.forEach((el,i)=>{el.classList.toggle('active',i===dropdownIdx);el.setAttribute('aria-selected',i===dropdownIdx?'true':'false');});
        options[dropdownIdx].scrollIntoView?.({block:'nearest'});
    }else if(event.key==='Enter'){event.preventDefault();(options[dropdownIdx]||options[0])?.click();}
});
document.addEventListener('keydown',event=>{
    if(event.key==='Escape'){
        if(!$('rxPopupOverlay').hidden){closeUpdatedPopup();return;}
        if(!$('createPanel').classList.contains('hidden')){closeCreatePatient();return;}
        if($('sidePanel').classList.contains('open'))closePatientPanel();closeDrugDropdown();replacingItem=null;$('replaceNotice').classList.add('hidden');
    }
    // Keep keyboard focus in the save-preview modal while it is open.
    if(event.key==='Tab' && !$('rxPopupOverlay').hidden){
        const nodes=[...$('rxPopupOverlay').querySelectorAll('button:not(:disabled),a[href]')];
        const first=nodes[0],last=nodes.at(-1);if(event.shiftKey && document.activeElement===first){event.preventDefault();last.focus();}else if(!event.shiftKey && document.activeElement===last){event.preventDefault();first.focus();}
    }
});
window.addEventListener('beforeunload',event=>{if(dirty || saving){event.preventDefault();event.returnValue='';}});
Object.assign(window,{showEditorTab,saveActivePatientState,updateTotals,switchPatient,removePatient,openPatientPanel,closePatientPanel,renderPatientList,selectPatient,showCreatePatient,closeCreatePatient,submitPatient,onDrugSearch,filterDrugType,quickDrugType,chooseDrug,addEmptyMedicineRow,replaceMedicine,removeMedicineRow,addRadiology,addCustomRadiology,removeRadiology,applyTemplate,savePrescription});
prepareState();hydrateHeader();renderAll();refreshSaveButton();
})();
</script>
@endpush
