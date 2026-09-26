<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request)
    {
        $range = $request->input('range', 'today');
        [$from, $to] = $this->resolveRange($request, $range);

        $sales = $this->reports->salesSummary($from, $to);
        $rx = $this->reports->prescriptionEarnings($from, $to);
        $pnl = $this->reports->profitLoss($from, $to);
        $trend = $this->reports->dailyTrend($range === 'today' ? 14 : 30);

        return view('reports.index', compact('sales', 'rx', 'pnl', 'trend', 'range', 'from', 'to'));
    }

    public function doctorWise(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $rx = $this->reports->prescriptionEarnings($from, $to);
        return view('reports.doctor-wise', compact('rx', 'from', 'to'));
    }

    public function radiology(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $rx = $this->reports->prescriptionEarnings($from, $to);
        return view('reports.radiology', compact('rx', 'from', 'to'));
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $sales = $this->reports->salesSummary($from, $to);
        $data = $this->reports->salesReport($from, $to, $request->input('group_by', 'day'));
        return view('reports.sales', compact('data', 'sales', 'from', 'to'))->with('group', $request->input('group_by', 'day'));
    }

    public function productSales(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $data = $this->reports->productWiseSales($from, $to);
        return view('reports.product-sales', compact('data', 'from', 'to'));
    }

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $pnl = $this->reports->profitLoss($from, $to);
        return view('reports.profit-loss', compact('pnl', 'from', 'to'));
    }

    public function expiry()
    {
        return view('reports.expiry', ['data' => $this->reports->expiryReport()]);
    }

    public function lowStock()
    {
        return view('reports.low-stock', ['data' => $this->reports->lowStockReport()]);
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $data = \App\Models\Purchase::with('vendor:id,name')
            ->whereBetween('purchase_date', [$from, $to])
            ->latest('purchase_date')->get();
        return view('reports.purchases', compact('data', 'from', 'to'));
    }

    protected function resolveRange(Request $request, string $default = 'this_month'): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [$request->date('from'), $request->date('to')];
        }
        return match ($request->input('range', $default)) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
