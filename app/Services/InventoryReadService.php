<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryReadService
{
    public const HISTORY_TYPES = ['prescription'=>'Prescription', 'purchase'=>'Purchase Invoice', 'adjustment'=>'Stock Adjustment'];
    public function state(): object
    {
        abort_unless(Schema::hasTable('inventory_tracking_state') && Schema::hasTable('inventory_movements'), 503,
            'Inventory history is not initialized. Run the supplied migration during maintenance.');
        $state = DB::table('inventory_tracking_state')->where('id', 1)->first();
        abort_unless($state, 503, 'Inventory baseline is not initialized.');
        abort_unless(Schema::hasColumn('inventory_movements', 'operation_id'), 503, 'Run the inventory operation-group upgrade migration during maintenance.');
        return $state;
    }
    public function filters(Request $request): array
    {
        $rules = [
            'tab'=>'nullable|in:stock,movements','q'=>'nullable|string|max:100',
            'category_id'=>'nullable|integer|min:1','product_id'=>'nullable|integer|min:1',
            'status'=>'nullable|in:all,zero,low,available,expired,mismatch',
            'type'=>'nullable|in:prescription,purchase,adjustment',
            'direction'=>'nullable|in:in,out','from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d',
            'per_page'=>'nullable|in:10,25,50,100','page'=>'nullable|integer|min:1|max:100000',
            'movements_page'=>'nullable|integer|min:1|max:100000','batches_page'=>'nullable|integer|min:1|max:100000',
        ];
        if ($request->filled('from') && $request->filled('to')) $rules['to'].='|after_or_equal:from';
        return $request->validate($rules);
    }
    public function products(array $f = [])
    {
        $today = now()->toDateString(); $soon = now()->addDays(30)->toDateString();
        $totals = DB::table('product_batches')->select('product_id')
            ->selectRaw('SUM(quantity) AS recorded_pcs, SUM(CASE WHEN quantity > 0 AND expiry_date >= ? THEN quantity ELSE 0 END) AS available_pcs, SUM(CASE WHEN quantity > 0 AND expiry_date < ? THEN quantity ELSE 0 END) AS expired_pcs, SUM(CASE WHEN quantity > 0 AND expiry_date BETWEEN ? AND ? THEN quantity ELSE 0 END) AS expiring_pcs, SUM(CASE WHEN quantity < 0 THEN 1 ELSE 0 END) AS negative_batches', [$today,$today,$today,$soon])->groupBy('product_id');
        $book = DB::table('inventory_movements')->select('product_id')
            ->selectRaw("SUM(CASE WHEN kind = 'baseline' THEN quantity_after ELSE quantity_change END) AS ledger_pcs")->groupBy('product_id');
        $batchBook = DB::table('inventory_movements')->whereNotNull('batch_id')->select('batch_id')
            ->selectRaw("SUM(CASE WHEN kind = 'baseline' THEN quantity_after ELSE quantity_change END) AS expected_qty")->groupBy('batch_id');
        if (!empty($f['product_id'])) {
            $totals->where('product_id', $f['product_id']);
            $book->where('product_id', $f['product_id']);
            $batchBook->where('product_id', $f['product_id']);
        }
        $issues = DB::table('product_batches as b')->leftJoinSub($batchBook, 'bl', 'bl.batch_id', '=', 'b.id')
            ->whereRaw('b.quantity <> COALESCE(bl.expected_qty,0)')->select('b.product_id')->selectRaw('COUNT(*) AS mismatched_batches')->groupBy('b.product_id');
        if (!empty($f['product_id'])) $issues->where('b.product_id', $f['product_id']);
        $q = DB::table('products as p')->leftJoin('units as u','u.id','=','p.purchase_unit_id')
            ->leftJoin('categories as c','c.id','=','p.category_id')->leftJoinSub($totals,'t','t.product_id','=','p.id')
            ->leftJoinSub($book,'l','l.product_id','=','p.id')->leftJoinSub($issues,'issues','issues.product_id','=','p.id')->whereNull('p.deleted_at')
            ->select('p.*','u.short_name as purchase_unit','c.name as category_name')
            ->selectRaw('COALESCE(t.recorded_pcs,0) AS recorded_pcs, COALESCE(t.available_pcs,0) AS available_pcs, COALESCE(t.expired_pcs,0) AS expired_pcs, COALESCE(t.expiring_pcs,0) AS expiring_pcs, COALESCE(t.negative_batches,0) AS negative_batches, COALESCE(l.ledger_pcs,0) AS ledger_pcs, COALESCE(t.recorded_pcs,0)-COALESCE(l.ledger_pcs,0) AS unrecorded_difference, COALESCE(issues.mismatched_batches,0) AS mismatched_batches');
        if (($f['q'] ?? '') !== '') $q->where(fn($w)=>$w->where('p.name','like','%'.$f['q'].'%')->orWhere('p.sku','like','%'.$f['q'].'%'));
        if (!empty($f['category_id'])) $q->where('p.category_id',$f['category_id']);
        if (!empty($f['product_id'])) $q->where('p.id',$f['product_id']);
        switch ($f['status'] ?? 'all') {
            case 'zero': $q->whereRaw('COALESCE(t.available_pcs,0) = 0'); break;
            case 'low': $q->whereRaw('COALESCE(t.available_pcs,0) <= p.min_stock'); break;
            case 'available': $q->whereRaw('COALESCE(t.available_pcs,0) > 0'); break;
            case 'expired': $q->whereRaw('COALESCE(t.expired_pcs,0) > 0'); break;
            case 'mismatch': $q->whereRaw('COALESCE(issues.mismatched_batches,0) > 0'); break;
        }
        return $q;
    }
    public function movements(array $f = [], ?int $productId = null)
    {
        $q=DB::table('inventory_movements as m')->leftJoin('products as p','p.id','=','m.product_id')
            ->select('m.*','p.deleted_at as product_deleted_at');
        if ($productId !== null) $q->where('m.product_id',$productId);
        elseif (!empty($f['product_id'])) $q->where('m.product_id',$f['product_id']);
        if (($f['q'] ?? '') !== '') $q->where(function($w)use($f){foreach(['m.product_name','m.sku','m.batch_number','m.reference','m.related_reference'] as $col)$w->orWhere($col,'like','%'.$f['q'].'%');});
        if (!empty($f['category_id'])) $q->where('p.category_id',$f['category_id']);
        if (($f['type'] ?? '') === 'sales') $q->whereIn('m.kind',['sale','sale_link']);
        elseif (!empty($f['type'])) $q->where('m.kind',$f['type']);
        if (!empty($f['from'])) $q->where('m.occurred_at','>=',$f['from'].' 00:00:00');
        if (!empty($f['to'])) $q->where('m.occurred_at','<=',$f['to'].' 23:59:59');
        if (($f['direction'] ?? '') === 'in') $q->where('m.quantity_change','>',0);
        if (($f['direction'] ?? '') === 'out') $q->where('m.quantity_change','<',0);
        if (($f['direction'] ?? '') === 'info') $q->where('m.quantity_change','=',0);
        return $q;
    }
    public function batches(int $productId)
    {
        $book=DB::table('inventory_movements')->where('product_id',$productId)->whereNotNull('batch_id')->select('batch_id')
            ->selectRaw("SUM(CASE WHEN kind = 'baseline' THEN quantity_after ELSE quantity_change END) AS ledger_quantity")->groupBy('batch_id');
        return DB::table('product_batches as b')->leftJoinSub($book,'l','l.batch_id','=','b.id')
            ->where('b.product_id',$productId)->select('b.*')->selectRaw('COALESCE(l.ledger_quantity,0) AS ledger_quantity, b.quantity-COALESCE(l.ledger_quantity,0) AS unrecorded_difference')->orderBy('b.expiry_date')->orderBy('b.id');
    }
    /**
     * Product-wide ledger balances, computed BEFORE display filters/pagination.
     * Includes all physical movements in the running balance, but displays ONLY
     * prescription stock changes, purchase receipts and stock adjustments.
     * MariaDB 10.2+ / MySQL 8+ window functions are required.
     */
    public function productMovements(array $f = [], ?int $productId = null)
    {
        $groups = DB::table('inventory_movements as e')->select('e.product_id')
            ->selectRaw("MIN(e.id) AS id, MAX(e.id) AS last_id,
                MAX(e.product_name) AS product_name, MAX(e.sku) AS sku,
                MAX(e.kind) AS kind,
                GROUP_CONCAT(DISTINCT e.reference ORDER BY e.reference SEPARATOR ', ') AS reference,
                MAX(e.related_reference) AS related_reference,
                MIN(e.occurred_at) AS occurred_at,
                SUM(CASE WHEN e.kind = 'baseline' THEN e.quantity_after ELSE e.quantity_change END) AS ledger_delta,
                SUM(e.quantity_change) AS quantity_change,
                SUM(CASE WHEN e.quantity_change > 0 THEN e.quantity_change ELSE 0 END) AS quantity_in,
                SUM(CASE WHEN e.quantity_change < 0 THEN -e.quantity_change ELSE 0 END) AS quantity_out")
            ->groupBy('e.product_id', 'e.kind')
            ->groupByRaw("COALESCE(e.operation_id, CONCAT('legacy-', e.id))");
        if (($f['q'] ?? '') !== '') {
            $like = '%'.$f['q'].'%';
            $groups->selectRaw('MAX(CASE WHEN e.product_name LIKE ? OR e.sku LIKE ? OR e.reference LIKE ? OR e.related_reference LIKE ? THEN 1 ELSE 0 END) AS matches_search', [$like,$like,$like,$like]);
        }
        $selected = $productId ?? ($f['product_id'] ?? null);
        if ($selected) $groups->where('e.product_id', $selected);
        $running = DB::query()->fromSub($groups, 'g')->select('g.*')
            ->selectRaw('SUM(g.ledger_delta) OVER (PARTITION BY g.product_id ORDER BY g.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS product_after');
        $q = DB::query()->fromSub($running, 'm')->leftJoin('products as p', 'p.id', '=', 'm.product_id')
            ->select('m.*', 'p.deleted_at as product_deleted_at')
            ->selectRaw('m.product_after - m.quantity_change AS product_before')
            ->whereIn('m.kind', ['purchase','prescription_issue','prescription_edit_in','prescription_edit_out','adjustment']);
        if (!empty($f['type'])) {
            if ($f['type'] === 'prescription') $q->whereIn('m.kind', ['prescription_issue','prescription_edit_in','prescription_edit_out']);
            else $q->where('m.kind', $f['type']);
        }
        if (!empty($f['category_id'])) $q->where('p.category_id', $f['category_id']);
        if (($f['q'] ?? '') !== '') $q->where('m.matches_search',1);
        if (!empty($f['from'])) $q->where('m.occurred_at','>=',$f['from'].' 00:00:00');
        if (!empty($f['to'])) $q->where('m.occurred_at','<=',$f['to'].' 23:59:59');
        if (($f['direction'] ?? '') === 'in') $q->where('m.quantity_in','>',0);
        if (($f['direction'] ?? '') === 'out') $q->where('m.quantity_out','>',0);
        return $q;
    }
    public function productPage(Request $request, int $id): array
    {
        $state=$this->state(); $filters=$this->filters($request);
        $product=$this->products(['product_id'=>$id])->first(); abort_unless($product,404);
        $perPage=(int)($filters['per_page']??25);
        $movements=$this->productMovements($filters,$id)->orderByDesc('m.id')->paginate($perPage,['*'],'movements_page')->withQueryString();
        $kinds=self::HISTORY_TYPES;
        return compact('product','state','filters','movements','kinds');
    }
}
