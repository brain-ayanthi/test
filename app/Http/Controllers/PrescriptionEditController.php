<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Services\PrescriptionEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PrescriptionEditController extends Controller
{
    // Register both routes inside your existing auth (and role/permission) group.
    public function edit(Prescription $prescription, PrescriptionEditor $editor)
    {
        $snapshot = DB::transaction(function () use ($prescription, $editor) {
            $rx = Prescription::whereKey($prescription->id)->lockForUpdate()->firstOrFail();
            try {
                $editor->assertEditable($rx);
            } catch (ValidationException $e) {
                abort(403, 'Sold, partially sold, cancelled or sale-linked prescriptions cannot be edited.');
            }
            return $editor->snapshot($rx->id);
        });
        [$seed, $legacyIds] = $this->formData($snapshot, $editor);
        $previous = old('payload');
        if (is_string($previous)) {
            $decoded = json_decode($previous, true);
            if (is_array($decoded) && is_array($decoded['patients'] ?? null)) $seed = $decoded;
        }
        // Include historical choices so editing does not discard archived selections.
        $allPatients = DB::table('patients')->orderBy('name')
            ->get(['id', 'name', 'patient_code', 'age', 'gender', 'phone', 'email', 'last_visit']);
        // Compatibility with older editor partials/layouts using $patients.
        // Both names reference the same actual patient collection.
        $patients = $allPatients;
        $doctors = DB::table('doctors')->orderBy('name')
            ->get(['id', 'name', 'specialization', 'doctor_fee']);
        $stock = DB::table('product_batches')->select('product_id')
            ->selectRaw('SUM(quantity) AS stock_quantity')->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())->groupBy('product_id');
        $products = DB::table('products as p')->leftJoinSub($stock, 's', function ($join) {
            $join->on('s.product_id', '=', 'p.id');
        })->orderBy('p.name')->get([
            'p.id', 'p.name', 'p.strength', 'p.form_type', 'p.drug_type_id',
            'p.selling_price', 'p.is_active', 'p.deleted_at',
            DB::raw('COALESCE(s.stock_quantity, 0) AS stock_quantity'),
        ]);
        $drugTypes = DB::table('drug_types')->where('is_active', 1)->orderBy('sort_order')
            ->get(['id', 'name', 'color', 'icon']);
        $readyTreatments = DB::table('ready_treatments')->where('is_active', 1)
            ->orderBy('name')->get(['id', 'name']);
        $radiologyTests = DB::table('radiology_tests')->orderBy('name')->get(['id', 'name', 'price']);
        // A dedicated view prevents the old edit.blade.php layout from being reused.
        return view('prescriptions.edit-create-style', compact(
            'prescription', 'seed', 'legacyIds', 'allPatients', 'patients', 'doctors',
            'products', 'drugTypes', 'readyTreatments', 'radiologyTests'
        ));
    }

    public function update(Request $request, Prescription $prescription, PrescriptionEditor $editor)
    {
        $request->validate(['payload' => 'required|string|max:2000000']);
        try {
            $input = json_decode($request->input('payload'), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValidationException::withMessages(['payload' => 'Invalid form data. Reload the Edit page.']);
        }
        if (!is_array($input)) throw ValidationException::withMessages(['payload' => 'Invalid form data.']);
        $data = Validator::make($input, $this->rules())->validate();
        // Hold the prescription lock until the next editor revision has been captured.
        $result = DB::transaction(function () use ($editor, $prescription, $data, $request) {
            $editor->update($prescription, $data, $request->user()?->id);
            [$seed, $legacyIds] = $this->formData($editor->snapshot($prescription->id), $editor);
            $stockQuantities = DB::table('product_batches')->select('product_id')
                ->selectRaw('SUM(quantity) AS available')->where('quantity', '>', 0)
                ->whereDate('expiry_date', '>=', now()->toDateString())->groupBy('product_id')
                ->pluck('available', 'product_id');
            return compact('seed', 'legacyIds', 'stockQuantities');
        }, 3);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Prescription updated successfully.'] + $result);
        }
        return redirect()->route('prescriptions.edit', $prescription)
            ->with('success', 'Prescription updated successfully. Stock and totals have been saved.');
    }

    private function formData(array $snapshot, PrescriptionEditor $editor): array
    {
        $seed = $snapshot['prescription'];
        $seed['prescription_date'] = substr((string) $seed['prescription_date'], 0, 10);
        $seed['revision'] = $editor->revision($snapshot);
        $seed['patients'] = [];
        $allocated = array_column($snapshot['allocations'], 'prescription_item_id');
        $legacyIds = [];
        foreach ($snapshot['patients'] as $p) {
            $p['items'] = array_values(array_filter($snapshot['items'], fn ($i) => (int) $i['prescription_patient_id'] === (int) $p['id']));
            foreach ($p['items'] as $i) {
                if ($i['stock_deducted'] && !in_array($i['id'], $allocated)) $legacyIds[] = (int) $i['id'];
            }
            $p['radiologies'] = array_values(array_filter($snapshot['radiologies'], fn ($r) => (int) $r['prescription_patient_id'] === (int) $p['id']));
            $seed['patients'][] = $p;
        }
        return [$seed, $legacyIds];
    }

    public function rules(): array
    {
        $money = ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'];
        return [
            'revision' => 'required|string|size:64',
            'prescription_date' => 'required|date_format:Y-m-d',
            'tax' => $money,
            'diagnosis' => 'nullable|string|max:10000',
            'lab_workup' => 'nullable|string|max:10000',
            'precautions' => 'nullable|string|max:10000',
            'physiotherapy' => 'nullable|string|max:10000',
            'notes' => 'nullable|string|max:10000',
            'next_visit' => 'nullable|string|max:1000',
            'patients' => 'required|array|min:1|max:50',
            'patients.*' => 'required|array',
            'patients.*.id' => 'nullable|integer|min:1',
            'patients.*.patient_id' => 'required|integer|exists:patients,id',
            'patients.*.doctor_id' => 'nullable|integer|exists:doctors,id',
            'patients.*.doctor_fee' => $money,
            'patients.*.discount' => $money,
            'patients.*.diagnosis' => 'nullable|string|max:10000',
            'patients.*.precautions' => 'nullable|string|max:10000',
            'patients.*.next_visit' => 'nullable|string|max:1000',
            'patients.*.items' => 'required|array|min:1|max:100',
            'patients.*.items.*' => 'required|array',
            'patients.*.items.*.id' => 'nullable|integer|min:1',
            'patients.*.items.*.product_id' => 'required|integer|exists:products,id',
            'patients.*.items.*.drug_name' => 'required|string|max:255',
            'patients.*.items.*.strength' => 'nullable|string|max:255',
            'patients.*.items.*.timing' => 'required|in:OD,BD,BID,TDS,QID',
            'patients.*.items.*.duration_days' => 'required|integer|min:1|max:3650',
            'patients.*.items.*.quantity' => 'required|numeric|decimal:0,2|min:0.01|max:10000',
            'patients.*.items.*.unit_price' => $money,
            'patients.*.items.*.discount' => $money,
            'patients.*.items.*.meal_relation' => 'nullable|string|max:255',
            'patients.*.items.*.instruction' => 'nullable|string|max:255',
            'patients.*.items.*.instruction_note' => 'nullable|string|max:10000',
            'patients.*.radiologies' => 'nullable|array|max:100',
            'patients.*.radiologies.*' => 'required|array',
            'patients.*.radiologies.*.id' => 'nullable|integer|min:1',
            'patients.*.radiologies.*.radiology_test_id' => 'nullable|integer|exists:radiology_tests,id',
            'patients.*.radiologies.*.test_name' => 'required|string|max:255',
            'patients.*.radiologies.*.price' => $money,
            'patients.*.radiologies.*.notes' => 'nullable|string|max:10000',
        ];
    }

}
