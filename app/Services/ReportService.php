<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\RadiologyTest;
use App\Models\Sale;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // -----------------------------------------------------------------
    //  Dashboard KPI summary
    // -----------------------------------------------------------------
    public function dashboardSummary(): array
    {
        return Cache::remember('dashboard_summary', now()->addMinutes(2), function () {
            $today = now()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();

            // Sales (POS invoices)
            $salesToday = Sale::whereDate('sale_date', $today)
                ->selectRaw('COALESCE(SUM(total),0) as total, COALESCE(SUM(paid_amount),0) as paid, COALESCE(SUM(discount),0) discount, COUNT(*) as cnt')
                ->first();

            // Prescription earnings today
            $rxToday = $this->prescriptionEarnings($today, $today);

            // This month
            $salesMonth = Sale::whereDate('sale_date', '>=', $monthStart)
                ->selectRaw('COALESCE(SUM(total),0) total, COALESCE(SUM(paid_amount),0) paid')->first();

            // Cash balances
            $cashAccounts = CashAccount::where('is_active', 1)->get(['id','name','balance']);
            $cashTotal = $cashAccounts->sum('balance');

            // Medicine stock value (purchase cost * qty)
            $stockValue = DB::table('product_batches')
                ->where('quantity', '>', 0)
                ->sum(DB::raw('quantity * purchase_price'));

            // Outstanding from customers (sales due)
            $customerDue = Sale::sum('due_amount');
            // Outstanding to vendors
            $vendorDue = 0;
            if (DB::getSchemaBuilder()->hasTable('vendor_payments')) {
                $vendorDue = (float) Purchase::sum('due_amount');
            }

            return [
                'sales_today' => (float) $salesToday->total,
                'sales_today_paid' => (float) $salesToday->paid,
                'sales_today_count' => (int) $salesToday->cnt,
                'rx_today' => $rxToday,
                'sales_month' => (float) $salesMonth->total,
                'profit_month' => (float) $this->salesSummary($monthStart, now()->endOfMonth())->profit,
                'cash_total' => (float) $cashTotal,
                'cash_accounts' => $cashAccounts,
                'stock_value' => (float) $stockValue,
                'customer_due' => (float) $customerDue,
                'vendor_due' => (float) $vendorDue,
            ];
        });
    }

    // -----------------------------------------------------------------
    //  Prescription earnings (medicine + doctor fee + radiology)
    // -----------------------------------------------------------------
    public function prescriptionEarnings($from, $to): object
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        return Cache::remember("rx_earn_{$from}_{$to}", now()->addMinutes(2), function () use ($from, $to) {
            // Top-level prescription totals (medicine + doctor fee + radiology + discount)
            $top = Prescription::whereBetween('prescription_date', [$from, $to])
                ->selectRaw('
                    COUNT(*) as rx_count,
                    COALESCE(SUM(medicine_cost),0) as medicine,
                    COALESCE(SUM(doctor_fee),0) as doctor_fee,
                    COALESCE(SUM(radiology_cost),0) as radiology,
                    COALESCE(SUM(discount),0) as discount,
                    COALESCE(SUM(total_fee),0) as total
                ')->first();

            // Doctor-wise breakdown
            $doctorWise = DB::table('prescription_patients as pp')
                ->leftJoin('doctors as d', 'd.id', '=', 'pp.doctor_id')
                ->join('prescriptions as rx', 'rx.id', '=', 'pp.prescription_id')
                ->whereBetween('rx.prescription_date', [$from, $to])
                ->selectRaw('
                    COALESCE(d.name, "Unknown") as doctor,
                    COUNT(DISTINCT pp.id) as patients,
                    COUNT(DISTINCT rx.id) as prescriptions,
                    COALESCE(SUM(pp.medicine_cost),0) as medicine,
                    COALESCE(SUM(pp.doctor_fee),0) as doctor_fee,
                    COALESCE(SUM(pp.radiology_cost),0) as radiology,
                    COALESCE(SUM(pp.total),0) as total
                ')
                ->groupBy('pp.doctor_id', 'd.name')
                ->orderByDesc('doctor_fee')
                ->get();

            // Radiology test-wise breakdown
            $testWise = collect();
            if (DB::getSchemaBuilder()->hasTable('prescription_patient_radiology')) {
                $testWise = DB::table('prescription_patient_radiology as r')
                    ->join('prescription_patients as pp', 'pp.id', '=', 'r.prescription_patient_id')
                    ->join('prescriptions as rx', 'rx.id', '=', 'pp.prescription_id')
                    ->whereBetween('rx.prescription_date', [$from, $to])
                    ->selectRaw('r.test_name, COUNT(*) as count, COALESCE(SUM(r.price),0) as total')
                    ->groupBy('r.test_name')
                    ->orderByDesc('total')
                    ->get();
            }

            return (object) [
                'rx_count' => (int) $top->rx_count,
                'medicine' => (float) $top->medicine,
                'doctor_fee' => (float) $top->doctor_fee,
                'radiology' => (float) $top->radiology,
                'discount' => (float) $top->discount,
                'total' => (float) $top->total,
                'doctor_wise' => $doctorWise,
                'test_wise' => $testWise,
            ];
        });
    }

    // -----------------------------------------------------------------
    //  Sales / POS profit
    // -----------------------------------------------------------------
    public function salesSummary($from, $to): object
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        return Cache::remember("sales_sum_{$from}_{$to}", now()->addMinutes(2), function () use ($from, $to) {
            // Profit = sum(sale price) - sum(purchase cost from batch)
            $profitSub = DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->leftJoin('product_batches as pb', 'pb.id', '=', 'si.batch_id')
                ->whereBetween('s.sale_date', [$from, $to])
                ->selectRaw('COALESCE(SUM(si.quantity * si.unit_price - si.quantity * COALESCE(pb.purchase_price, 0)),0) as p');

            $agg = Sale::whereBetween('sale_date', [$from, $to])
                ->selectRaw('
                    COUNT(*) as invoice_count,
                    COALESCE(SUM(subtotal),0) as subtotal,
                    COALESCE(SUM(discount),0) as discount,
                    COALESCE(SUM(tax),0) as tax,
                    COALESCE(SUM(total),0) as total,
                    COALESCE(SUM(paid_amount),0) as paid,
                    COALESCE(SUM(due_amount),0) as due
                ')->first();
            $agg->profit = (float) ($profitSub->first()->p ?? 0);
            return $agg;
        });
    }

    // -----------------------------------------------------------------
    //  Daily / monthly trend
    // -----------------------------------------------------------------
    public function dailyTrend($days = 30): \Illuminate\Support\Collection
    {
        return Cache::remember("trend_{$days}d", now()->addMinutes(10), function () use ($days) {
            $start = now()->subDays($days - 1)->startOfDay();
            $end = now()->endOfDay();

            // Daily sales totals (without join to avoid overcounting)
            $salesTotals = DB::table('sales')
                ->whereBetween('sale_date', [$start, $end])
                ->selectRaw('DATE(sale_date) as date, SUM(total) as total')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            // Daily profit from items × batches
            $salesProfit = DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->leftJoin('product_batches as pb', 'pb.id', '=', 'si.batch_id')
                ->whereBetween('s.sale_date', [$start, $end])
                ->selectRaw('DATE(s.sale_date) as date, SUM(si.quantity * si.unit_price - si.quantity * COALESCE(pb.purchase_price, 0)) as profit')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $rx = DB::table('prescriptions')
                ->whereBetween('prescription_date', [$start, $end])
                ->selectRaw('DATE(prescription_date) as date, SUM(total_fee) as total')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $trend = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $d = now()->subDays($i)->toDateString();
                $trend->push((object) [
                    'date' => $d,
                    'sales' => (float) ($salesTotals[$d]->total ?? 0),
                    'profit' => (float) ($salesProfit[$d]->profit ?? 0),
                    'prescriptions' => (float) ($rx[$d]->total ?? 0),
                ]);
            }
            return $trend;
        });
    }

    // -----------------------------------------------------------------
    //  Legacy methods kept for existing report pages
    // -----------------------------------------------------------------
    public function profitLoss($from = null, $to = null): array
    {
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now()->endOfMonth();

        $sales = $this->salesSummary($from, $to);
        $rx = $this->prescriptionEarnings($from, $to);

        // COGS for POS sales = quantity * purchase price from sold batch
        $salesCogs = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->leftJoin('product_batches as pb', 'pb.id', '=', 'si.batch_id')
            ->whereBetween('s.sale_date', [$from, $to])
            ->sum(DB::raw('si.quantity * COALESCE(pb.purchase_price, 0)'));

        // Prescription medicine profit = use product_batches purchase_price
        // (the batch from which this item was sold), and the batch/medicine selling price.
        $rxStats = DB::table('prescription_items as pi')
            ->join('prescriptions as rx', 'rx.id', '=', 'pi.prescription_id')
            ->leftJoin('product_batches as pb', 'pb.id', '=', 'pi.batch_id')
            ->leftJoin('products as p', 'p.id', '=', 'pi.product_id')
            ->whereBetween('rx.prescription_date', [$from, $to])
            ->selectRaw("
                COALESCE(SUM(pi.quantity * pi.unit_price),0) as sell_total,
                COALESCE(SUM(pi.quantity * COALESCE(pb.purchase_price, p.purchase_price, 0)),0) as cost_total
            ")
            ->first();

        $rxCogs = (float) ($rxStats->cost_total ?? 0);
        $rxSell = (float) ($rxStats->sell_total ?? 0);
        // If the prescription item itself has no stored unit price (older data), fall back to rx->medicine
        $rxSell = $rxSell > 0 ? $rxSell : (float) $rx->medicine;

        $cogs = (float) $salesCogs + (float) $rxCogs;
        $rxMedicineProfit = $rxSell - (float) $rxCogs;
        $salesProfit = (float) $sales->profit;
        $grossProfit = $salesProfit + $rxMedicineProfit + (float) $rx->doctor_fee + (float) $rx->radiology;
        $totalDiscount = (float) $sales->discount + (float) $rx->discount;

        // Operating expenses are deducted after gross profit to produce true net profit.
        $operatingExpenses = 0.0;
        $expenseBreakdown = collect();
        if (DB::getSchemaBuilder()->hasTable('expenses')) {
            $operatingExpenses = (float) DB::table('expenses')
                ->whereBetween('expense_date', [Carbon::parse($from)->toDateString(), Carbon::parse($to)->toDateString()])
                ->sum('amount');

            $expenseBreakdown = DB::table('expenses as e')
                ->join('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
                ->whereBetween('e.expense_date', [Carbon::parse($from)->toDateString(), Carbon::parse($to)->toDateString()])
                ->selectRaw('ec.name, ec.color, COUNT(e.id) as entries, SUM(e.amount) as total')
                ->groupBy('ec.id', 'ec.name', 'ec.color')
                ->orderByDesc('total')->get();
        }

        $profitBeforeExpenses = $grossProfit - $totalDiscount;
        $netProfit = $profitBeforeExpenses - $operatingExpenses;

        return [
            'revenue' => [
                'sales' => (float) $sales->total,
                'rx_medicine' => $rxSell,
                'doctor_fee' => (float) $rx->doctor_fee,
                'radiology' => (float) $rx->radiology,
            ],
            'costs' => [
                'sales_cogs' => (float) $salesCogs,
                'rx_medicine_cogs' => (float) $rxCogs,
                'operating_expenses' => $operatingExpenses,
            ],
            'profit' => [
                'sales_profit' => $salesProfit,
                'rx_medicine_profit' => $rxMedicineProfit,
                'doctor_fee' => (float) $rx->doctor_fee,
                'radiology' => (float) $rx->radiology,
                'gross_profit' => $grossProfit,
                'before_expenses' => $profitBeforeExpenses,
            ],
            'expenses' => [
                'total' => $operatingExpenses,
                'breakdown' => $expenseBreakdown,
            ],
            'cogs' => (float) $cogs,
            'gross_profit' => $grossProfit,
            'discount' => $totalDiscount,
            'profit_before_expenses' => $profitBeforeExpenses,
            'net_profit' => $netProfit,
            'sales' => $sales,
            'rx' => $rx,
        ];
    }

    public function cashFlow($from, $to): array
    {
        return DB::table('cash_transactions')
            ->select('type', DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$from, $to])
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }

    public function salesReport($from, $to, string $groupBy = 'day'): array
    {
        $dateFormat = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';
        return DB::table('sales')
            ->selectRaw("DATE_FORMAT(sale_date, '{$dateFormat}') as period, COUNT(*) as invoices, SUM(total) as total, SUM(paid_amount) as paid, SUM(due_amount) as due")
            ->whereBetween('sale_date', [$from, $to])
            ->groupBy('period')->orderBy('period')->get()->toArray();
    }

    public function productWiseSales($from, $to): array
    {
        return DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->selectRaw('products.name, SUM(sale_items.quantity) qty, SUM(sale_items.total) total')
            ->groupBy('products.id', 'products.name')->orderByDesc('total')->get()->toArray();
    }

    public function expiryReport(): array
    {
        return ProductBatch::with('product:id,name')
            ->where('quantity', '>', 0)->whereDate('expiry_date', '<', now()->addDays(90))
            ->orderBy('expiry_date')->get()->map(fn ($b) => [
                'product' => $b->product->name ?? '', 'batch' => $b->batch_number,
                'expiry' => $b->expiry_date->format('Y-m-d'), 'quantity' => $b->quantity,
            ])->toArray();
    }

    public function lowStockReport(): array
    {
        return DB::table('products')
            ->leftJoin('product_batches', fn ($j) => $j->on('product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)->whereDate('product_batches.expiry_date', '>=', now()))
            ->select('products.name', 'products.sku', 'products.min_stock',
                DB::raw('COALESCE(SUM(product_batches.quantity),0) as stock'))
            ->where('products.is_active', 1)
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.min_stock')
            ->havingRaw('COALESCE(SUM(product_batches.quantity),0) <= products.min_stock')
            ->get()->toArray();
    }
}
