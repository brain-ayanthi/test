<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('baseUnit')->latest()->get();
        return view('inventory.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'short_name' => 'required|string|max:20',
            'conversion' => 'nullable|numeric|min:0',
            'is_base_unit' => 'boolean',
            'base_unit_id' => 'nullable|exists:units,id',
        ]);

        Unit::create($data);
        return back()->with('success', 'Unit added.');
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'short_name' => 'required|string|max:20',
            'conversion' => 'nullable|numeric|min:0',
            'is_base_unit' => 'boolean',
            'base_unit_id' => 'nullable|exists:units,id',
            'is_active' => 'boolean',
        ]);
        $unit->update($data);
        return back()->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return back()->with('success', 'Unit deleted.');
    }
}
