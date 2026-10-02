<?php
namespace App\Http\Controllers;
use App\Services\InventoryReadService;
use App\Services\InventoryMovementLedger;
use App\Support\InventoryAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InventoryReportController extends Controller
{
    public function index(Request $request, InventoryReadService $read)
    {
        InventoryAccess::authorize($request->user());
        $state=$read->state(); $filters=$read->filters($request); $tab=$filters['tab']??'stock';
        $perPage=(int)($filters['per_page']??25);
        $products=null; $movements=null;
        if($tab==='stock') $products=$read->products($filters)->orderBy('p.name')->orderBy('p.id')->paginate($perPage)->withQueryString();
        else $movements=$read->productMovements($filters)->orderByDesc('m.id')->paginate($perPage)->withQueryString();
        $categories=DB::table('categories')->orderBy('name')->get(['id','name']);
        $kinds=InventoryReadService::HISTORY_TYPES;
        return response()->view('reports.inventory',compact('state','filters','tab','products','movements','categories','kinds'))
            ->header('Cache-Control','private, no-store');
    }
}
