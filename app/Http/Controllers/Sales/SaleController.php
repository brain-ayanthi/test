<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    public function __construct(protected SaleService $service) {}

    public function index(Request $request)
    {
        $query = Sale::with('patient')->latest();
        if ($status = $request->input('status')) {
            $query->where('payment_status', $status);
        }
        $sales = $query->select(['id','invoice_number','customer_name','customer_type','sale_date','total','paid_amount','due_amount','payment_status','patient_id'])
            ->with('patient:id,name')
            ->latest('id')->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function pos()
    {
        $products = Product::where('is_active', true)
            ->select(['id','name','form_type','strength','selling_price','sku'])
            ->orderBy('name')->get();
        $patients = Patient::latest('id')->take(100)->get(['id','name','patient_code','phone']);
        $accounts = CashAccount::where('is_active', true)->get(['id','name']);
        return view('sales.pos', compact('products', 'patients', 'accounts'));
    }

    public function storePos(Request $request)
    {
        $data = $request->validate([
            'customer_type' => 'required|in:walking,patient',
            'patient_id' => 'nullable|exists:patients,id',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'payment_method' => 'required|in:cash,bank,mfs',
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'paid_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric',
        ]);

        $items = array_map(function ($item) {
            $product = Product::find($item['product_id']);
            return [
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
            ];
        }, $data['items']);

        $sale = $this->service->createDirectSale($data, $items);
        return response()->json(['success' => true, 'sale' => $sale->load('items')]);
    }

    public function convertFromPrescription(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,bank,mfs',
            'cash_account_id' => 'required|exists:cash_accounts,id',
        ]);

        $sale = $this->service->createFromPrescription($prescription, $data);
        return response()->json(['success' => true, 'sale' => $sale->load('items')]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['items', 'patient', 'payments', 'returns']);
        return view('sales.show', compact('sale'));
    }

    public function printInvoice(Sale $sale)
    {
        $sale->load(['items', 'patient']);
        $pdf = Pdf::loadView('sales.print', compact('sale'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');
        return $pdf->stream("invoice-{$sale->invoice_number}.pdf");
    }
}
