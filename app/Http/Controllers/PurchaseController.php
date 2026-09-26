<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $service) {}

    public function index(Request $request)
    {
        $query = Purchase::with('vendor')->latest();

        if ($status = $request->input('status')) {
            $query->where('payment_status', $status);
        }
        if ($vendorId = $request->input('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        $purchases = $query->with('vendor:id,name')
            ->select(['id','invoice_number','vendor_id','purchase_date','total','paid_amount','due_amount','payment_status'])
            ->latest('id')->paginate(20);
        $vendors = Vendor::where('is_active', true)->get(['id','name']);
        return view('purchase.index', compact('purchases', 'vendors'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->get(['id','name']);
        $products = Product::where('is_active', true)
            ->with('purchaseUnit:id,name,short_name')
            ->select(['id','name','sku','purchase_unit_id','pieces_per_purchase_unit','purchase_price','selling_price'])
            ->orderBy('name')->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'ppu' => $p->pieces_per_purchase_unit,
                    'pprice' => $p->purchase_price,
                    'sprice' => $p->selling_price,
                    'unit' => $p->purchaseUnit ? $p->purchaseUnit->short_name : '',
                ];
            })->values();
        $accounts = CashAccount::where('is_active', true)->get(['id','name']);
        return view('purchase.create', compact('vendors', 'products', 'accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_date' => 'required|date',
            'due_date' => 'nullable|date',
            'reference' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.purchase_quantity' => 'required|numeric|min:0.01',
            'items.*.pieces_per_unit' => 'required|numeric|min:0.01',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'required|string',
            'items.*.expiry_date' => 'required|date',
            'items.*.discount' => 'nullable|numeric',
            'items.*.tax' => 'nullable|numeric',
        ]);

        $purchase = $this->service->createPurchase($data, $data['items']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'purchase' => $purchase]);
        }

        return redirect()->route('vendor-payments.show', $purchase->vendor_id)
            ->with('success', 'Purchase saved and stock updated. Record payment from vendor outstanding.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['vendor', 'items.product', 'items.purchaseUnit', 'payments', 'returns']);
        return view('purchase.show', compact('purchase'));
    }
}
