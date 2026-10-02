<?php

namespace App\Http\Controllers;

use App\Services\StockAdjustmentBulkService;
use App\Services\StockAdjustmentService;
use App\Support\StockAdjustmentAccess;
use App\Support\StockQuantity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StockAdjustmentController extends Controller
{
    private function access(Request $request): void
    {
        StockAdjustmentAccess::authorize($request->user());
        abort_unless(Schema::hasTable('stock_adjustments'), 503,
            'Stock Adjustments is not installed. Run the supplied migration OR import add_stock_adjustments.sql, not both.');
    }

    public function index(Request $request)
    {
        $this->access($request);
        $rules = [
            'search' => 'nullable|string|max:100', 'mode' => 'nullable|in:set,increase,decrease',
            'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d',
            'page' => 'nullable|integer|min:1|max:100000',
        ];
        if ($request->filled('from') && $request->filled('to')) $rules['to'] .= '|after_or_equal:from';
        $filters = $request->validate($rules);
        $query = DB::table('stock_adjustments');
        if (($filters['search'] ?? '') !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', '%'.$search.'%')->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('batch_number', 'like', '%'.$search.'%')->orWhere('user_name', 'like', '%'.$search.'%')
                    ->orWhere('reference', 'like', '%'.$search.'%');
                if (preg_match('/^(?:ADJ-)?0*(\d+)$/i', $search, $m)) $q->orWhere('id', (int) $m[1]);
            });
        }
        if (!empty($filters['mode'])) $query->where('mode', $filters['mode']);
        if (!empty($filters['from'])) $query->whereDate('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $query->whereDate('created_at', '<=', $filters['to']);
        $adjustments = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $products = DB::table('products')->whereNull('deleted_at')->orderBy('name')
            ->get(['id', 'name', 'sku', 'strength', 'form_type', 'is_active']);
        $reasons = StockAdjustmentService::REASONS;
        $requestId = (string) Str::uuid();
        // Bulk adjustments need the companion service, which ships with the
        // Inventory History package. The page hides the tab when it is absent.
        $bulkEnabled = class_exists(StockAdjustmentBulkService::class);
        $bulk = [
            'enabled' => $bulkEnabled,
            'maxItems' => $bulkEnabled ? StockAdjustmentBulkService::MAX_ITEMS : 0,
            'requestId' => (string) Str::uuid(),
        ];
        return response()->view('stock-adjustments.index', compact('adjustments', 'products', 'reasons', 'requestId', 'bulk'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function batches(Request $request, int $product, StockAdjustmentService $service)
    {
        $this->access($request);
        $row = DB::table('products')->where('id', $product)->whereNull('deleted_at')->first();
        abort_unless($row, 404, 'Product not found.');
        $unit = $row->selling_unit_id ? DB::table('units')->where('id', $row->selling_unit_id)->value('short_name') : null;
        // Zero-quantity, inactive-product and expired batches must remain selectable
        // for genuine counts/write-offs. Expired stock needs explicit confirmation.
        $batches = DB::table('product_batches')->where('product_id', $row->id)
            ->orderBy('expiry_date')->orderBy('id')->get()->map(fn ($batch) => [
                'id' => (int) $batch->id, 'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date,
                'quantity' => StockQuantity::decimal(StockQuantity::minor($batch->quantity, true)),
                'initial_quantity' => (string) $batch->initial_quantity,
                'expired' => (string) $batch->expiry_date < now()->toDateString(),
                'revision' => $service->revision($batch, $row),
            ]);
        // Product-wide totals, shown alongside the selected batch figures.
        // Recorded total includes expired/negative batches so it matches the
        // batch figures this screen displays; available excludes them.
        $today = now()->toDateString();
        $all = DB::table('product_batches')->where('product_id', $row->id)->get();
        $recorded = 0; $available = 0;
        foreach ($all as $b) {
            $recorded += StockQuantity::minor($b->quantity, true);
            if ((float) $b->quantity > 0 && (string) $b->expiry_date >= $today) {
                $available += StockQuantity::minor($b->quantity, true);
            }
        }
        return response()->json([
            'product' => ['id' => (int) $row->id, 'name' => $row->name, 'sku' => $row->sku,
                'unit' => $unit ?: 'stock units', 'is_active' => (bool) $row->is_active,
                'recorded_quantity' => StockQuantity::decimal($recorded),
                'available_quantity' => StockQuantity::decimal($available)],
            'batches' => $batches,
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function storeBulk(Request $request)
    {
        $this->access($request);
        abort_unless(class_exists(StockAdjustmentBulkService::class), 503,
            'Bulk adjustments are not installed. Copy app/Services/StockAdjustmentBulkService.php from the Inventory History package.');
        $service = app(StockAdjustmentBulkService::class);
        $result = $service->apply($request->user(), $request->only(['request_id', 'items']));
        $saved = $result['adjustments'];
        $count = $saved->count();
        $first = 'ADJ-'.str_pad((string) $saved->first()->id, 6, '0', STR_PAD_LEFT);
        $last = 'ADJ-'.str_pad((string) $saved->last()->id, 6, '0', STR_PAD_LEFT);
        $range = $count > 1 ? "$first to $last" : $first;
        $noun = $count === 1 ? 'adjustment' : 'adjustments';
        $message = $result['replayed']
            ? "$count $noun ($range) were already saved. Stock was not adjusted again."
            : "$count $noun saved ($range). Stock and the adjustment history were updated together.";
        if ($request->expectsJson()) {
            $request->session()->flash('success', $message);
            return response()->json([
                'success' => true, 'replayed' => $result['replayed'], 'count' => $count,
                'adjustment_ids' => $saved->pluck('id')->all(), 'message' => $message,
                'redirect_url' => route('stock-adjustments.index'),
            ])->header('Cache-Control', 'private, no-store');
        }
        return redirect()->route('stock-adjustments.index')->with('success', $message);
    }

    public function store(Request $request, StockAdjustmentService $service)
    {
        $this->access($request);
        $result = $service->apply($request->user(), $request->only(array_keys($service->rules())));
        $id = $result['adjustment']->id;
        $number = 'ADJ-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
        $message = $result['replayed']
            ? "$number was already saved. Stock was not adjusted again."
            : "$number saved. Stock and the adjustment history were updated together.";
        if ($request->expectsJson()) {
            $request->session()->flash('success', $message);
            return response()->json([
                'success' => true, 'replayed' => $result['replayed'], 'adjustment_id' => $id,
                'message' => $message, 'redirect_url' => route('stock-adjustments.index'),
            ])->header('Cache-Control', 'private, no-store');
        }
        return redirect()->route('stock-adjustments.index')->with('success', $message);
    }
}
