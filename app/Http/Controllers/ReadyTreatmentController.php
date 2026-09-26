<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Product;
use App\Models\PrescriptionItem;
use App\Models\ReadyTreatment;
use Illuminate\Http\Request;

class ReadyTreatmentController extends Controller
{
    public function index()
    {
        $treatments = ReadyTreatment::with('doctor:id,name')
            ->withCount('items')
            ->select(['id', 'name', 'disease', 'doctor_id', 'is_active', 'created_at'])
            ->latest('id')->paginate(20);
        return view('prescriptions.treatments.index', compact('treatments'));
    }

    /**
     * Return template with items as JSON (used by prescription builder AJAX).
     */
    public function show(ReadyTreatment $treatment)
    {
        $treatment->load('items:id,ready_treatment_id,product_id,drug_name,timing,duration_days,meal_relation,instruction');
        return response()->json($treatment);
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->select(['id', 'name', 'form_type', 'strength', 'selling_price'])
            ->orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->get(['id', 'name']);
        return view('prescriptions.treatments.create', compact('products', 'doctors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'disease' => 'nullable|string',
            'description' => 'nullable|string',
            'doctor_id' => 'nullable|exists:doctors,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.timing' => 'required|in:OD,BD,BID,TDS,QID',
            'items.*.duration_days' => 'required|integer|min:1',
            'items.*.times_of_day' => 'nullable|array',
            'items.*.meal_relation' => 'nullable|string',
            'items.*.instruction' => 'nullable|string',
        ]);

        $treatment = ReadyTreatment::create([
            'name' => $data['name'],
            'disease' => $data['disease'] ?? null,
            'description' => $data['description'] ?? null,
            'doctor_id' => $data['doctor_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $treatment->items()->create([
                'product_id' => $product->id,
                'drug_name' => $product->name,
                'form_type' => $product->form_type,
                'strength' => $product->strength,
                'timing' => $item['timing'],
                'timing_multiplier' => PrescriptionItem::TIMING_MAP[$item['timing']] ?? 1,
                'times_of_day' => $item['times_of_day'] ?? null,
                'meal_relation' => $item['meal_relation'] ?? null,
                'duration_days' => $item['duration_days'],
                'instruction' => $item['instruction'] ?? null,
            ]);
        }

        return redirect()->route('treatments.index')->with('success', 'Ready Treatment created.');
    }

    public function edit(ReadyTreatment $treatment)
    {
        $treatment->load('items');
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->get();
        return view('prescriptions.treatments.edit', compact('treatment', 'products', 'doctors'));
    }

    public function update(Request $request, ReadyTreatment $treatment)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'disease' => 'nullable|string|max:255',
            'doctor_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer',
            'items.*.product_id' => 'required|integer',
            'items.*.timing' => 'required|in:OD,BD,BID,TDS,QID',
            'items.*.duration_days' => 'required|integer|min:1',
            'items.*.meal_relation' => 'nullable|string',
            'items.*.instruction' => 'nullable|string',
            'deleted_items' => 'nullable|string',
        ]);

        $treatment->update([
            'name' => $data['name'],
            'disease' => $data['disease'] ?? null,
            'doctor_id' => $data['doctor_id'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        // Delete removed items
        if (!empty($data['deleted_items'])) {
            $deletedIds = array_filter(array_map('intval', explode(',', $data['deleted_items'])));
            if ($deletedIds) {
                $treatment->items()->whereIn('id', $deletedIds)->delete();
            }
        }

        // Update existing + add new items
        $products = Product::whereIn('id', collect($data['items'] ?? [])->pluck('product_id')->unique())
            ->get()->keyBy('id');

        foreach (($data['items'] ?? []) as $row) {
            $product = $products[$row['product_id']] ?? Product::find($row['product_id']);
            if (!$product) continue;

            $itemData = [
                'product_id' => $product->id,
                'drug_name' => $product->name,
                'form_type' => $product->form_type,
                'strength' => $product->strength,
                'timing' => $row['timing'],
                'timing_multiplier' => PrescriptionItem::TIMING_MAP[$row['timing']] ?? 3,
                'duration_days' => $row['duration_days'],
                'meal_relation' => $row['meal_relation'] ?? null,
                'instruction' => $row['instruction'] ?? null,
            ];

            if (!empty($row['id'])) {
                $treatment->items()->where('id', $row['id'])->update($itemData);
            } else {
                $treatment->items()->create($itemData);
            }
        }

        return redirect()->route('treatments.index')->with('success', 'Template updated.');
    }

    public function destroy(ReadyTreatment $treatment)
    {
        $treatment->delete();
        return redirect()->route('treatments.index')->with('success', 'Template deleted.');
    }
}
