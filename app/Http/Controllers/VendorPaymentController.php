<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Purchase;
use App\Models\VendorPayment;
use Illuminate\Http\Request;

class VendorPaymentController extends Controller
{
    /**
     * Show list of vendors with outstanding balances and payment history.
     */
    public function index()
    {
        $vendors = Vendor::where('is_active', true)
            ->withCount('purchases')
            ->withSum('purchases as total_purchased', 'total')
            ->withSum('payments as total_paid', 'amount')
            ->get()
            ->map(function ($v) {
                $v->outstanding = (float) $v->opening_balance
                    + (float) ($v->total_purchased ?? 0)
                    - (float) ($v->total_paid ?? 0);
                return $v;
            })
            ->sortByDesc('outstanding')
            ->values();

        $recentPayments = VendorPayment::with(['vendor:id,name', 'purchase:id,invoice_number'])
            ->latest('id')
            ->take(20)
            ->get();

        return view('purchase.vendor-payments.index', compact('vendors', 'recentPayments'));
    }

    /**
     * Show payment history for a single vendor + modal form.
     */
    public function show(Vendor $vendor)
    {
        $vendor->loadSum('purchases as total_purchased', 'total');
        $vendor->loadSum('payments as total_paid', 'amount');
        $vendor->outstanding = (float) $vendor->opening_balance
            + (float) ($vendor->total_purchased ?? 0)
            - (float) ($vendor->total_paid ?? 0);

        $payments = $vendor->payments()
            ->with('purchase:id,invoice_number')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(30);

        $purchases = $vendor->purchases()
            ->select('id', 'invoice_number', 'purchase_date', 'total', 'paid_amount', 'due_amount', 'payment_status')
            ->latest('purchase_date')
            ->take(50)
            ->get();

        return view('purchase.vendor-payments.show', compact('vendor', 'payments', 'purchases'));
    }

    /**
     * Store a new vendor payment.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,mfs,cheque',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['created_by'] = auth()->id();

        $payment = VendorPayment::create($data);

        // If linked to a purchase, update its paid/due amounts and status.
        if (!empty($data['purchase_id'])) {
            $purchase = Purchase::find($data['purchase_id']);
            if ($purchase && $purchase->vendor_id == $data['vendor_id']) {
                $newPaid = (float) $purchase->paid_amount + (float) $data['amount'];
                $newDue = max(0, (float) $purchase->total - $newPaid);
                $status = $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'pending');
                $purchase->update([
                    'paid_amount' => $newPaid,
                    'due_amount' => $newDue,
                    'payment_status' => $status,
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'payment' => $payment]);
        }

        return redirect()->route('vendor-payments.show', $data['vendor_id'])
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Delete a payment (and reverse linked purchase paid amount).
     */
    public function destroy(VendorPayment $payment)
    {
        if ($payment->purchase_id) {
            $purchase = Purchase::find($payment->purchase_id);
            if ($purchase) {
                $newPaid = max(0, (float) $purchase->paid_amount - (float) $payment->amount);
                $newDue = max(0, (float) $purchase->total - $newPaid);
                $status = $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'pending');
                $purchase->update([
                    'paid_amount' => $newPaid,
                    'due_amount' => $newDue,
                    'payment_status' => $status,
                ]);
            }
        }

        $vendorId = $payment->vendor_id;
        $payment->delete();

        return redirect()->route('vendor-payments.show', $vendorId)
            ->with('success', 'Payment deleted.');
    }
}
