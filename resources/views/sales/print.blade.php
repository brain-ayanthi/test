<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Invoice {{ $sale->invoice_number }}</title>
<style>
    @page { margin: 2mm; }
    body { font-family: 'Courier New', monospace; font-size: 11px; width: 72mm; margin: 0 auto; }
    .center { text-align: center; } .bold { font-weight: bold; } .right { text-align: right; }
    .line { border-top: 1px dashed #000; margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .small { font-size: 9px; }
</style>
</head>
<body>
<div class="center bold" style="font-size:14px;">{{ config('app.company_name') }}</div>
<div class="center small">123 Medical Road, Colombo</div>
<div class="center small">Tel: +94 11 222 3333</div>
<div class="line"></div>
<div class="center bold">SALES INVOICE</div>
<table>
    <tr><td>Invoice:</td><td class="right bold">{{ $sale->invoice_number }}</td></tr>
    <tr><td>Date:</td><td class="right">{{ $sale->sale_date->format('d M Y h:i A') }}</td></tr>
    <tr><td>Customer:</td><td class="right">{{ $sale->customer_name }}</td></tr>
    @if($sale->prescription)<tr><td>Rx:</td><td class="right">{{ $sale->prescription->prescription_number }}</td></tr>@endif
</table>
<div class="line"></div>
<table>
    <tr><th class="left">Item</th><th class="right">Qty</th><th class="right">Amt</th></tr>
    @foreach($sale->items as $i)
    <tr><td>{{ \Illuminate\Support\Str::limit($i->product_name,22) }}</td><td class="right">{{ $i->quantity }}</td><td class="right">{{ number_format($i->total,2) }}</td></tr>
    @endforeach
</table>
<div class="line"></div>
<table>
    <tr><td>Subtotal</td><td class="right">Rs {{ number_format($sale->subtotal,2) }}</td></tr>
    @if($sale->doctor_fee > 0)<tr><td>Doctor Fee</td><td class="right">Rs {{ number_format($sale->doctor_fee,2) }}</td></tr>@endif
    @if($sale->discount > 0)<tr><td>Discount</td><td class="right">- Rs {{ number_format($sale->discount,2) }}</td></tr>@endif
    <tr class="bold"><td>TOTAL</td><td class="right">Rs {{ number_format($sale->total,2) }}</td></tr>
    <tr><td>Paid ({{ ucfirst($sale->payment_method) }})</td><td class="right">Rs {{ number_format($sale->paid_amount,2) }}</td></tr>
    @if($sale->due_amount > 0)<tr><td>Due</td><td class="right">Rs {{ number_format($sale->due_amount,2) }}</td></tr>@endif
</table>
<div class="line"></div>
<div class="center small">Thank you for your purchase!</div>
<script>window.onload = function(){ window.print(); };</script>
</body>
</html>
