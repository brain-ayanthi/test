<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Prescription;
use App\Models\Sale;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(ReportService $reports)
    {
        $summary = $reports->dashboardSummary();
        $today = now()->toDateString();

        // Medicine statistics
        $totalProducts = Product::where('is_active', 1)->count();
        $outOfStock = Product::where('is_active', 1)
            ->whereDoesntHave('batches', fn ($q) => $q->where('quantity', '>', 0)->where('expiry_date', '>=', now()->startOfDay()))
            ->count();
        $lowStock = Product::where('is_active', 1)->where('min_stock', '>', 0)
            ->withSum(['batches as stock' => fn ($q) => $q->where('quantity', '>', 0)->where('expiry_date', '>=', now()->startOfDay())], 'quantity')
            ->get()
            ->filter(fn ($p) => $p->stock !== null && $p->stock > 0 && $p->stock <= $p->min_stock)
            ->count();
        $available = max(0, $totalProducts - $outOfStock - $lowStock);

        // startOfDay(): a batch that expires today is still live, not expired.
        // (whereDate with a clock-carrying now() treats today as already past.)
        $expired = ProductBatch::where('quantity', '>', 0)
            ->where('expiry_date', '<', now()->startOfDay())->count();
        $expiringSoon = ProductBatch::where('quantity', '>', 0)
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addMonths(3)->endOfDay()])->count();

        // Low stock products list
        $lowStockProducts = Product::where('is_active', 1)
            ->withSum(['batches as stock' => fn ($q) => $q->where('quantity', '>', 0)->where('expiry_date', '>=', now()->startOfDay())], 'quantity')
            ->orderBy('name')
            ->get()
            ->filter(fn ($p) => $p->stock !== null && $p->stock > 0 && $p->min_stock > 0 && $p->stock <= $p->min_stock)
            ->take(5);

        // Expiring soon batches grouped by month
        $expiring = ProductBatch::where('quantity', '>', 0)
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addMonths(4)->endOfDay()])
            ->orderBy('expiry_date')
            ->get()
            ->groupBy(fn ($b) => $b->expiry_date->format('F'));

        // Today's P&L
        $pnlToday = $reports->profitLoss($today, $today);
        $profitToday = $pnlToday['net_profit'] ?? 0;
        $revenueToday = $pnlToday['revenue']['sales']
            + $pnlToday['revenue']['rx_medicine']
            + $pnlToday['revenue']['doctor_fee']
            + $pnlToday['revenue']['radiology'];
        $costToday = $pnlToday['cogs'] ?? 0;

        $invoiceCount = Sale::whereDate('sale_date', $today)->count();
        $discountToday = Sale::whereDate('sale_date', $today)->sum('discount')
            + Prescription::whereDate('prescription_date', $today)->sum('discount');
        $customerDues = (float) Sale::sum('due_amount');
        $refundToday = 0; // refunds table not implemented

        // Recent data
        $recentSales = Sale::select(['id', 'invoice_number', 'customer_name', 'total', 'payment_status', 'sale_date'])
            ->latest('id')->take(6)->get();
        $recentPrescriptions = Prescription::with(['patient:id,name,patient_code', 'doctor:id,name'])
            ->select(['id', 'prescription_number', 'patient_id', 'doctor_id', 'patient_name', 'total_fee', 'status', 'prescription_date'])
            ->latest('id')->take(6)->get();
        $prescriptionCount = Prescription::count();
        $salesCount = Sale::count();

        // Expired batches list
        $expiredBatches = ProductBatch::where('quantity', '>', 0)
            ->where('expiry_date', '<', now()->startOfDay())
            ->with('product:id,name')
            ->latest('expiry_date')->take(5)->get();

        $summary = array_merge($summary, [
            'medicine' => [
                'available' => $available,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'expired' => $expired,
                'expiring_soon' => $expiringSoon,
            ],
            'profit_today' => $profitToday,
            'revenue_today' => $revenueToday,
            'cost_today' => $costToday,
            'invoice_count' => $invoiceCount,
            'discount_today' => $discountToday,
            'dues_today' => $customerDues,
            'refund_today' => $refundToday,
            'prescription_count' => $prescriptionCount,
            'sales_count' => $salesCount,
            'low_stock_products' => $lowStockProducts,
            'expiring' => $expiring,
            'expired_batches' => $expiredBatches,
        ]);

        return view('dashboard.index', compact('summary', 'recentSales', 'recentPrescriptions'));
    }
}
