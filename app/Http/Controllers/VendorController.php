<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount('purchases')
            ->select(['id','name','company','phone','email','opening_balance'])
            ->latest('id')->paginate(20);
        return view('purchase.vendors.index', compact('vendors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
        ]);

        Vendor::create($data);
        return back()->with('success', 'Vendor added.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $vendor->update($data);
        return back()->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return back()->with('success', 'Vendor deleted.');
    }
}
