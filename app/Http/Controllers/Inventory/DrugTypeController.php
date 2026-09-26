<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\DrugType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DrugTypeController extends Controller
{
    public function index()
    {
        $drugTypes = DrugType::orderBy('sort_order')->get();
        return view('inventory.drug-types.index', compact('drugTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        DrugType::create($data);
        return back()->with('success', 'Drug Type added.');
    }

    public function update(Request $request, DrugType $drugType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        $drugType->update($data);
        return back()->with('success', 'Drug Type updated.');
    }

    public function destroy(DrugType $drugType)
    {
        $drugType->delete();
        return back()->with('success', 'Drug Type deleted.');
    }
}
