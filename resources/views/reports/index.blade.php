@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')

@push('styles')
<style>
    .kpi { transition: transform .15s; }
    .kpi:hover { transform: translateY(-2px); }
    .range-btn.active { background:#2563eb; color:#fff; }
</style>
@endpush

@section('content')
{{-- Range selector --}}
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    @foreach(['today'=>'Today','yesterday'=>'Yesterday','7days'=>'Last 7 Days','this_month'=>'This Month','last_month'=>'Last Month'] as $v=>$lbl)
        <a href="?range={{ $v }}" class="range-btn px-4 py-2 rounded-lg text-sm font-semibold border {{ $range===$v?'active':'bg-white' }}">{{ $lbl }}</a>
    @endforeach
    <div class="flex gap-2 ml-auto items-end">
        <input type="date" name="from" value="{{ request('from', \Carbon\Carbon::parse($from)->toDateString()) }}" class="border rounded p-2 text-sm">
        <input type="date" name="to" value="{{ request('to', \Carbon\Carbon::parse($to)->toDateString()) }}" class="border rounded p-2 text-sm">
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold">Apply</button>
    </div>
</form>

{{-- KPI cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="kpi bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-5 shadow">
        <div class="text-xs uppercase opacity-80">Sales (POS)</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($sales->total,2) }}</div>
        <div class="text-xs opacity-80 mt-1">{{ $sales->invoice_count }} invoices</div>
    </div>
    <div class="kpi bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-5 shadow">
        <div class="text-xs uppercase opacity-80">Sales Profit</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($sales->profit,2) }}</div>
        <div class="text-xs opacity-80 mt-1">Paid: Rs {{ number_format($sales->paid,2) }}</div>
    </div>
    <div class="kpi bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-5 shadow">
        <div class="text-xs uppercase opacity-80">Prescription Income</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($rx->total,2) }}</div>
        <div class="text-xs opacity-80 mt-1">{{ $rx->rx_count }} prescriptions</div>
    </div>
    <div class="kpi bg-gradient-to-br from-rose-500 to-rose-600 text-white rounded-xl p-5 shadow">
        <div class="text-xs uppercase opacity-80">Doctor Fees</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($rx->doctor_fee,2) }}</div>
        <div class="text-xs opacity-80 mt-1">Radiology: Rs {{ number_format($rx->radiology,2) }}</div>
    </div>
    <a href="{{ route('expenses.report', ['from'=>\Carbon\Carbon::parse($from)->toDateString(), 'to'=>\Carbon\Carbon::parse($to)->toDateString()]) }}" class="kpi bg-gradient-to-br from-red-600 to-red-700 text-white rounded-xl p-5 shadow block">
        <div class="text-xs uppercase opacity-80">Expenses</div>
        <div class="text-2xl font-bold mt-1">Rs {{ number_format($pnl['expenses']['total'] ?? 0,2) }}</div>
        <div class="text-xs opacity-80 mt-1">Net Profit: Rs {{ number_format($pnl['net_profit'],2) }}</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Earnings breakdown --}}
    <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow-sm">
        <h3 class="font-bold text-lg mb-4"><i class="fas fa-coins text-yellow-500 mr-1"></i>Income Breakdown</h3>
        <div class="space-y-3">
            @php
                $rows = [
                    ['Medicine Sales (POS)', $sales->total, 'blue'],
                    ['POS Profit (net)', $sales->profit, 'emerald' ],
                    ['Prescription Medicine', $rx->medicine, 'indigo'],
                    ['Doctor Fees (commission)', $rx->doctor_fee, 'rose'],
                    ['Radiology / Extra Tests', $rx->radiology, 'amber'],
                    ['Discount Given', -($sales->discount + $rx->discount), 'gray'],
                    ['Operating Expenses', -($pnl['expenses']['total'] ?? 0), 'red'],
                ];
            @endphp
            @foreach($rows as $r)
            <div class="flex justify-between items-center py-2 border-b">
                <span class="text-gray-600">{{ $r[0] }}</span>
                <span class="font-bold text-{{ $r[2] }}-600">Rs {{ number_format($r[1],2) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between items-center py-3 text-lg font-bold">
                <span>GRAND TOTAL INCOME</span>
                <span class="text-green-700">Rs {{ number_format($sales->total + $rx->total,2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 text-lg font-bold border-t-2">
                <span>{{ $pnl['net_profit'] >= 0 ? 'NET PROFIT AFTER EXPENSES' : 'NET LOSS AFTER EXPENSES' }}</span>
                <span class="{{ $pnl['net_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">Rs {{ number_format($pnl['net_profit'],2) }}</span>
            </div>
        </div>
    </div>

    {{-- Doctor commission --}}
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg"><i class="fas fa-user-md text-blue-500 mr-1"></i>Doctor Wise</h3>
            <a href="{{ route('reports.doctor-wise') }}?from={{ request('from') }}&to={{ request('to') }}" class="text-xs text-blue-600 hover:underline">View All</a>
        </div>
        <div class="space-y-2 max-h-80 overflow-y-auto">
            @forelse($rx->doctor_wise as $d)
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <div>
                    <div class="font-semibold text-sm">{{ $d->doctor }}</div>
                    <div class="text-xs text-gray-500">{{ $d->patients }} patients · Rs {{ number_format($d->medicine,2) }} med</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-rose-600">Rs {{ number_format($d->doctor_fee,2) }}</div>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-6">No data.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Radiology tests --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg"><i class="fas fa-x-ray text-amber-500 mr-1"></i>Radiology / Extra Tests</h3>
            <a href="{{ route('reports.radiology') }}?from={{ request('from') }}&to={{ request('to') }}" class="text-xs text-blue-600 hover:underline">View All</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-100"><tr>
                <th class="p-2 text-left">Test</th><th class="p-2">Count</th><th class="p-2 text-right">Revenue</th>
            </tr></thead>
            <tbody>
            @forelse($rx->test_wise as $t)
            <tr class="border-b">
                <td class="p-2 font-semibold">{{ $t->test_name }}</td>
                <td class="p-2 text-center">{{ $t->count }}</td>
                <td class="p-2 text-right font-bold text-amber-600">Rs {{ number_format($t->total,2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="p-4 text-center text-gray-400">No tests in this period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Trend --}}
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <h3 class="font-bold text-lg mb-4"><i class="fas fa-chart-line text-emerald-500 mr-1"></i>14-Day Trend</h3>
        <canvas id="trendChart" height="200"></canvas>
    </div>
</div>

<div class="flex flex-wrap gap-2">
    <a href="{{ route('reports.sales') }}" class="bg-white border px-4 py-2 rounded text-sm font-semibold hover:bg-gray-50"><i class="fas fa-file-invoice mr-1"></i>Sales Report</a>
    <a href="{{ route('reports.profit-loss') }}" class="bg-white border px-4 py-2 rounded text-sm font-semibold hover:bg-gray-50"><i class="fas fa-chart-pie mr-1"></i>Profit & Loss</a>
    <a href="{{ route('reports.doctor-wise') }}" class="bg-white border px-4 py-2 rounded text-sm font-semibold hover:bg-gray-50"><i class="fas fa-user-md mr-1"></i>Doctor Commission</a>
    <a href="{{ route('reports.radiology') }}" class="bg-white border px-4 py-2 rounded text-sm font-semibold hover:bg-gray-50"><i class="fas fa-x-ray mr-1"></i>Radiology</a>
    <a href="{{ route('reports.product-sales') }}" class="bg-white border px-4 py-2 rounded text-sm font-semibold hover:bg-gray-50"><i class="fas fa-pills mr-1"></i>Product Sales</a>
    <a href="{{ route('expenses.report') }}" class="bg-white border px-4 py-2 rounded text-sm font-semibold hover:bg-gray-50 text-red-700"><i class="fas fa-receipt mr-1"></i>Expense Report</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const trend = @json($trend);
const labels = trend.map(t => t.date.slice(5));
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            { label:'Sales', data: trend.map(t=>t.sales), borderColor:'#3b82f6', tension:.3, fill:false },
            { label:'Profit', data: trend.map(t=>t.profit), borderColor:'#10b981', tension:.3, fill:false },
            { label:'Prescriptions', data: trend.map(t=>t.prescriptions), borderColor:'#a855f7', tension:.3, fill:false },
        ]
    },
    options: { responsive:true, plugins:{legend:{position:'bottom'}} }
});
</script>
@endpush
