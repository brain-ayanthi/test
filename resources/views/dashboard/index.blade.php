@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Top earnings cards (replaces Store Cash / Bank / MFS / Total) --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-600 text-sm font-medium">Today - Rx Medicine</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">Rs {{ number_format($summary['rx_today']->medicine ?? 0, 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-pills"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-rose-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-600 text-sm font-medium">Today - Doctor Fees</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">Rs {{ number_format($summary['rx_today']->doctor_fee ?? 0, 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-md"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-amber-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-600 text-sm font-medium">Today - Radiology / Extra</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">Rs {{ number_format($summary['rx_today']->radiology ?? 0, 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-x-ray"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-white/80 text-sm font-medium">Today - GRAND TOTAL INCOME</p>
                <p class="text-2xl font-bold mt-2">Rs {{ number_format(($summary['rx_today']->total ?? 0) + ($summary['sales_today'] ?? 0), 2) }}</p>
                <p class="text-xs text-white/70 mt-1">Rx + POS Sales</p>
            </div>
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Medicine Info --}}
    <div class="bg-gray-900 text-white rounded-xl p-5 shadow">
        <h3 class="font-bold text-lg mb-4">Medicine Info</h3>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <div class="w-16 h-16 mx-auto rounded-full bg-green-500 flex items-center justify-center mb-2">
                    <i class="fas fa-check text-2xl"></i>
                </div>
                <div class="text-2xl font-bold">{{ $summary['medicine']['available'] }}</div>
                <div class="text-xs text-gray-400">Available</div>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto rounded-full bg-amber-500 flex items-center justify-center mb-2">
                    <i class="fas fa-exclamation text-2xl"></i>
                </div>
                <div class="text-2xl font-bold">{{ $summary['medicine']['low_stock'] }}</div>
                <div class="text-xs text-gray-400">Low Stock</div>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto rounded-full bg-red-500 flex items-center justify-center mb-2">
                    <i class="fas fa-times text-2xl"></i>
                </div>
                <div class="text-2xl font-bold">{{ $summary['medicine']['out_of_stock'] }}</div>
                <div class="text-xs text-gray-400">Out of Stock</div>
            </div>
        </div>
    </div>

    {{-- Profit & Loss --}}
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-red-800 text-lg">Total Profit and Loss</h3>
            <a href="{{ route('reports.profit-loss') }}" class="text-blue-600 text-sm hover:underline">View Details</a>
        </div>
        <div class="flex gap-2 mb-3">
            <span class="px-3 py-1 bg-yellow-400 text-gray-900 text-xs font-bold rounded">Today</span>
        </div>
        <div class="text-center py-2">
            <div class="text-3xl font-bold {{ $summary['profit_today'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                Rs {{ number_format($summary['profit_today'], 2) }}
            </div>
            <div class="text-xs text-red-600 mt-1">Net Profit (Today)</div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="bg-white rounded p-3 text-center">
                <div class="text-xs text-gray-500">Revenue</div>
                <div class="font-bold text-gray-800">Rs {{ number_format($summary['revenue_today'], 2) }}</div>
            </div>
            <div class="bg-white rounded p-3 text-center">
                <div class="text-xs text-gray-500">Cost</div>
                <div class="font-bold text-red-600">Rs {{ number_format($summary['cost_today'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Recent Prescription --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-blue-800 text-lg">Recent Prescription</h3>
            <a href="{{ route('prescriptions.index') }}" class="text-blue-600 text-sm hover:underline">See All</a>
        </div>
        <div class="space-y-2 max-h-52 overflow-y-auto">
            @forelse($recentPrescriptions as $rx)
            <div class="flex justify-between items-center bg-white rounded p-2 text-sm">
                <div>
                    <div class="font-medium text-gray-800">{{ $rx->prescription_number }}</div>
                    <div class="text-xs text-gray-500">{{ $rx->patient_name ?? 'Walk-in' }} &middot; {{ optional($rx->prescription_date)->format('M d, Y') }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-gray-800">Rs {{ number_format($rx->total_fee, 2) }}</div>
                    <span class="text-xs px-2 py-0.5 rounded {{ $rx->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($rx->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-sm text-center py-4">No prescriptions yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Middle row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Stock Alert --}}
    <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-yellow-800">Stock Alert</h3>
            <a href="{{ route('reports.low-stock') }}" class="text-yellow-700 hover:underline text-xs">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 border-b">
                        <th class="py-1">Medicine</th>
                        <th class="py-1">Category</th>
                        <th class="py-1">Stock</th>
                        <th class="py-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary['low_stock_products'] as $p)
                    <tr class="border-b">
                        <td class="py-2">{{ $p->name }}</td>
                        <td class="text-gray-500 text-xs">{{ optional($p->category)->name ?? 'Medicine' }}</td>
                        <td><span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-bold">{{ $p->stock }}</span></td>
                        <td><span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded text-xs">Order Now</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-3 text-center text-gray-500 text-xs">All stocks are healthy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Prescriptions count --}}
    <div class="bg-teal-50 border border-teal-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-teal-800">Prescriptions</h3>
            <a href="{{ route('prescriptions.index') }}" class="text-teal-600 text-xs hover:underline">See All</a>
        </div>
        <div class="space-y-3 mb-4">
            <div class="flex justify-between items-center bg-white/60 rounded p-2">
                <span class="text-gray-600 text-sm">Patient</span>
                <span class="font-bold text-lg text-gray-800">{{ $summary['prescription_count'] }}</span>
            </div>
            <div class="flex justify-between items-center bg-white/60 rounded p-2">
                <span class="text-gray-600 text-sm">Sales (POS)</span>
                <span class="font-bold text-lg text-gray-800">{{ $summary['sales_count'] }}</span>
            </div>
        </div>
        <div class="flex justify-center">
            <a href="{{ route('prescriptions.create') }}" class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-teal-600 text-white text-2xl font-bold hover:bg-teal-700 transition">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <p class="text-center text-xs text-teal-700 mt-2">New Prescription</p>
    </div>

    {{-- Expired --}}
    <div class="bg-red-600 text-white rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-lg">Expired</h3>
            <span class="text-3xl font-bold text-yellow-300">{{ $summary['medicine']['expired'] }}</span>
        </div>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            @forelse($summary['expired_batches'] as $b)
            <div class="bg-white/10 rounded p-2">
                <div class="font-medium text-sm">{{ $b->product->name ?? 'Unknown' }}</div>
                <div class="text-xs text-white/70">{{ optional($b->expiry_date)->format('d M Y') }}</div>
            </div>
            @empty
            <p class="text-white/70 text-xs text-center py-2">No expired items.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Expiring soon + invoices + summary --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-6">
    <div class="lg:col-span-2 bg-gradient-to-br from-red-800 to-red-900 text-white rounded-xl p-5 shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Expiring Soon</h3>
            <span class="text-3xl font-bold text-yellow-300">{{ $summary['medicine']['expiring_soon'] }}</span>
        </div>
        <div class="space-y-2">
            @forelse($summary['expiring'] as $month => $batches)
            <div class="flex justify-between bg-white/10 rounded px-3 py-2">
                <span>{{ $month }}</span>
                <span class="font-bold">({{ $batches->count() }})</span>
            </div>
            @empty
            <p class="text-white/70 text-sm">Nothing expiring soon.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white"><i class="fas fa-file-invoice"></i></div>
            <span class="text-blue-700 font-semibold text-sm">INVOICES TODAY</span>
        </div>
        <div class="text-3xl font-bold text-blue-900 mb-3">{{ $summary['invoice_count'] }}</div>
        <div class="bg-white rounded p-3">
            <div class="text-sm text-gray-500">Paid Today</div>
            <div class="text-xl font-bold text-green-600">Rs {{ number_format($summary['sales_today_paid'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600"><i class="fas fa-percent"></i></div>
                    <span class="text-gray-600 text-sm">DISCOUNT</span>
                </div>
                <span class="font-bold text-gray-800">Rs {{ number_format($summary['discount_today'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600"><i class="fas fa-hand-holding-usd"></i></div>
                    <span class="text-gray-600 text-sm">DUES (Customer)</span>
                </div>
                <span class="font-bold text-red-600">Rs {{ number_format($summary['customer_due'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600"><i class="fas fa-truck"></i></div>
                    <span class="text-gray-600 text-sm">DUES (Vendor)</span>
                </div>
                <span class="font-bold text-red-600">Rs {{ number_format($summary['vendor_due'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600"><i class="fas fa-undo"></i></div>
                    <span class="text-gray-600 text-sm">REFUND</span>
                </div>
                <span class="font-bold text-red-600">Rs {{ number_format($summary['refund_today'], 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Recent sales --}}
<div class="bg-white rounded-xl p-5 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-lg text-gray-800">Recent POS Sales</h3>
        <a href="{{ route('sales.index') }}" class="text-blue-600 text-sm hover:underline">See All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Invoice #</th>
                    <th class="py-2">Customer</th>
                    <th class="py-2 text-right">Total</th>
                    <th class="py-2 text-center">Status</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSales as $s)
                <tr class="border-b">
                    <td class="py-2 font-mono text-blue-600">{{ $s->invoice_number }}</td>
                    <td class="text-gray-700">{{ $s->customer_name ?? 'Walking Customer' }}</td>
                    <td class="py-2 text-right font-bold">Rs {{ number_format($s->total, 2) }}</td>
                    <td class="py-2 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            {{ $s->payment_status === 'paid' ? 'bg-green-100 text-green-700' :
                               ($s->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($s->payment_status) }}
                        </span>
                    </td>
                    <td class="py-2 text-right">
                        <a href="{{ route('sales.show', $s) }}" class="text-blue-500"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-400">No sales yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
