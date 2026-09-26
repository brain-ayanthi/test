<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Prescription {{ $prescription->prescription_number }}</title>
<style>
/*
 * XP-80T uses 80mm paper but its reliable printable width is about 72mm.
 * Keeping the receipt content inside 64mm gives equal 4mm printable margins
 * and prevents the Qty/Amt column from being clipped on the right.
 */
@page { size: 72mm auto; margin: 1mm; }
* { box-sizing: border-box; }
html, body { background: #fff; color: #000; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.25;
    width: 64mm;
    margin: 0;
    padding: 0;
    color: #000;
}
.center { text-align: center; }
.bold { font-weight: 700; }
.line { border-top: 1px dashed #000; margin: 5px 0; height: 1px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
td, th { padding: 2px 1px; vertical-align: top; color: #000; }
th { font-weight: 700; border-bottom: 1px solid #000; }
.right { text-align: right; white-space: nowrap; }
.small { font-size: 10.5px; }
.patient-block { margin-bottom: 7px; }
.medicine-name { font-weight: 600; overflow-wrap: anywhere; word-break: normal; }
.dose { font-size: 10.5px; font-weight: 600; margin-top: 1px; }
.amount-table td { padding-top: 3px; padding-bottom: 3px; }
.barcode-wrap { text-align: center; margin: 5px 0; }
.barcode-svg { max-width: 100%; height: 48px; }
.company-name { font-size: 15px; line-height: 1.15; font-weight: 700; }
.rx-title { font-size: 13px; font-weight: 700; }
.rx-number-bottom { font-size: 14px; letter-spacing: .8px; font-weight: 700; }
.total-row { font-size: 14px; font-weight: 700; border-top: 1px solid #000; }
@media print {
    html, body { width: 64mm !important; margin: 0 !important; padding: 0 !important; }
}
</style>
</head>
<body>

<div class="center company-name">{{ config('app.company_name', 'Clinic Pharmacy') }}</div>
<div class="center small">123 Medical Road, Colombo</div>
<div class="center small">Tel: +94 11 222 3333</div>
<div class="line"></div>

<div class="center rx-title">PRESCRIPTION</div>
<table>
    <colgroup><col style="width:25mm"><col style="width:39mm"></colgroup>
    <tr>
        <td>Rx #:</td>
        <td class="right bold">{{ $prescription->prescription_number }}</td>
    </tr>
    <tr>
        <td>Date:</td>
        <td class="right">{{ $prescription->prescription_date->format('d M Y') }}</td>
    </tr>
    <tr>
        <td>Patients:</td>
        <td class="right">{{ $prescription->prescriptionPatients->count() }}</td>
    </tr>
</table>
<div class="line"></div>

@foreach($prescription->prescriptionPatients as $i => $pp)
<div class="patient-block">
    <div class="bold" style="font-size:12.5px">{{ $i + 1 }}. {{ $pp->patient_name }}</div>
    <div class="small">{{ $pp->patient_age }} yrs &middot; {{ $pp->patient_phone }}</div>

    @if(!is_null($pp->doctor))
    <div class="small">Dr. {{ $pp->doctor->name }}</div>
    @endif

    <table>
        <colgroup>
            <col style="width:4mm">
            <col style="width:39mm">
            <col style="width:7mm">
            <col style="width:14mm">
        </colgroup>
        <thead>
        <tr>
            <th style="text-align:left">#</th>
            <th style="text-align:left">Medicine</th>
            <th class="right">Qty</th>
            <th class="right">Amt</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pp->items as $j => $item)
        <tr>
            <td>{{ $j + 1 }}</td>
            <td class="medicine-name">
                {{ $item->drug_name }}
                @if(!empty($item->form_type) || !empty($item->strength))
                <span class="small">[{{ trim(($item->form_type ?? '').' '.($item->strength ?? '')) }}]</span>
                @endif
                <div class="dose">
                    @if(!empty($item->instruction_note))
                    - {{ $item->instruction_note }}
                    @endif
                </div>
            </td>
            <td class="right bold">{{ (float) $item->quantity }}{{ str_contains(strtolower((string)$item->strength), 'ml') ? 'ml' : '' }}</td>
            <td class="right"><div class="dose">
                    {{ $item->timing }} x {{ $item->duration_days }}d
                </div></td>
        </tr>
        @endforeach

        @foreach($pp->radiologies as $rad)
        <tr>
            <td colspan="2"><span class="small">[R] {{ $rad->test_name }}</span></td>
            <td></td>
            <td class="right">{{ number_format((float)$rad->price, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if(!empty($pp->diagnosis))
    <div class="small"><b>Dx:</b> {{ $pp->diagnosis }}</div>
    @endif
</div>
<div class="line"></div>
@endforeach

<table class="amount-table">
    <colgroup><col style="width:35mm"><col style="width:29mm"></colgroup>
    <tr>
        <td>Medicine</td>
        <td class="right bold">Rs {{ number_format((float)$prescription->medicine_cost, 2) }}</td>
    </tr>
    <tr>
        <td>Radiology</td>
        <td class="right bold">Rs {{ number_format((float)$prescription->radiology_cost, 2) }}</td>
    </tr>
    <tr>
        <td>Doctor Fee</td>
        <td class="right bold">Rs {{ number_format((float)$prescription->doctor_fee, 2) }}</td>
    </tr>
    <tr class="total-row">
        <td>TOTAL</td>
        <td class="right">Rs {{ number_format((float)$prescription->total_fee, 2) }}</td>
    </tr>
</table>

<div class="line"></div>
<div class="barcode-wrap">
    {!! $barcodeSvg ?? '' !!}
    <div class="rx-number-bottom">{{ $prescription->prescription_number }}</div>
</div>
<div class="line"></div>
<div class="center small">Thank you! Get well soon.</div>
<div class="center small">{{ now()->format('d M Y h:i A') }}</div>

@if(empty($directPrintServer))
<script>
window.onload = function () {
    setTimeout(function () { window.print(); }, 200);
};
</script>
@endif
</body>
</html>
