<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DrugType;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\RadiologyTest;
use App\Models\ReadyTreatment;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends Controller
{
    public function __construct(protected PrescriptionService $service) {}

    public function index(Request $request)
    {
        $query = Prescription::with(['patient:id,name,patient_code', 'doctor:id,name'])
            ->select(['id', 'prescription_number', 'patient_id', 'doctor_id', 'prescription_date',
                      'medicine_cost', 'doctor_fee', 'radiology_cost', 'total_fee', 'status', 'sale_status', 'patient_name']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhere('prescription_number', 'like', "%{$search}%");
            });
        }

        $prescriptions = $query->withCount('prescriptionPatients as patients_count')
            ->latest('id')->paginate(20);
        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request)
    {
        $patientId = $request->input('patient_id');
        $patient = $patientId ? Patient::findOrFail($patientId) : null;

        $doctors = Doctor::where('is_active', true)->get(['id', 'name', 'specialization']);
        $drugTypes = DrugType::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'color', 'icon']);
        $products = Product::where('is_active', true)
            ->select(['id', 'name', 'form_type', 'strength', 'drug_type_id', 'selling_price', 'sku', 'min_stock'])
            ->withSum(['batches as stock_quantity' => function ($q) {
                $q->where('quantity', '>', 0)->whereDate('expiry_date', '>=', now());
            }], 'quantity')
            ->with('drugType:id,name,color')
            ->orderBy('name')
            ->get();
        $readyTreatments = ReadyTreatment::where('is_active', true)->get(['id', 'name', 'disease']);
        $radiologyTests = RadiologyTest::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $allPatients = Patient::select(['id', 'patient_code', 'name', 'phone', 'age', 'gender', 'email'])
            ->latest('id')->take(100)->get();

        $nextRxNumber = \App\Models\Prescription::previewNextNumber();

        return view('prescriptions.create', compact(
            'patient', 'doctors', 'drugTypes', 'products',
            'readyTreatments', 'radiologyTests', 'allPatients', 'nextRxNumber'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prescription_date' => 'required|date',
            'patients' => 'required|array|min:1',
            'patients.*.patient_id' => 'required|integer',
            'patients.*.doctor_id' => 'nullable|integer',
            'patients.*.doctor_fee' => 'nullable|numeric|min:0',
            'patients.*.discount' => 'nullable|numeric|min:0',
            'patients.*.diagnosis' => 'nullable|string',
            'patients.*.precautions' => 'nullable|string',
            'patients.*.next_visit' => 'nullable|string',
            'patients.*.items' => 'required|array|min:1',
            'patients.*.items.*.product_id' => 'required|integer',
            'patients.*.items.*.strength' => 'nullable|string|max:100',
            'patients.*.items.*.timing' => 'required|in:OD,BD,BID,TDS,QID',
            'patients.*.items.*.duration_days' => 'required|integer|min:1',
            'patients.*.items.*.quantity' => 'nullable|numeric',
            'patients.*.items.*.total' => 'nullable|numeric',
            'patients.*.items.*.meal_relation' => 'nullable|string',
            'patients.*.items.*.instruction' => 'nullable|string',
            'patients.*.items.*.instruction_note' => 'nullable|string',
            'patients.*.items.*.unit_price' => 'nullable|numeric',
            'patients.*.radiologies' => 'nullable|array',
            'patients.*.radiologies.*.radiology_test_id' => 'nullable|integer',
            'patients.*.radiologies.*.test_name' => 'nullable|string',
            'patients.*.radiologies.*.price' => 'nullable|numeric',
        ]);

        try {
            \Log::info('Prescription save started', [
                'patients_count' => count($data['patients']),
                'items_per_patient' => array_map(fn($p) => count($p['items'] ?? []), $data['patients']),
            ]);

            // Create prescription + all data
            $prescription = $this->service->create(
                ['prescription_date' => $data['prescription_date']],
                $data['patients']
            );

            \Log::info('Prescription saved', ['id' => $prescription->id, 'number' => $prescription->prescription_number]);

            // Load all patients/items/radiology so the popup can preview the prescription.
            $prescription->load([
                'prescriptionPatients.items.product',
                'prescriptionPatients.doctor',
                'prescriptionPatients.radiologies',
            ]);

            // Stock deduction after response
            if ($request->wantsJson()) {
                $presId = $prescription->id;
                app()->terminating(function () use ($presId) {
                    $rx = Prescription::find($presId);
                    if ($rx) {
                        try {
                            $this->service->saveAndDeductStock($rx);
                        } catch (\Throwable $e) {
                            \Log::error('Stock deduction failed for Rx #' . $presId . ': ' . $e->getMessage());
                        }
                    }
                });

                return response()->json([
                    'success' => true,
                    'prescription' => $prescription,
                ]);
            }

            return redirect()->route('prescriptions.show', $prescription)
                ->with('success', 'Prescription saved and sent to printer.');
        } catch (\Throwable $e) {
            \Log::error('Prescription save FAILED: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()) . ':' . $e->getLine(),
                ], 500);
            }
            throw $e;
        }
    }

    public function show(Prescription $prescription)
    {
        $prescription->load([
            'patient', 'doctor',
            'prescriptionPatients.patient',
            'prescriptionPatients.doctor',
            'prescriptionPatients.items.product',
            'prescriptionPatients.items.batch',
            'prescriptionPatients.radiologies',
            'documents',
        ]);

        // Casts are already defined as float in the Eloquent models.
        return view('prescriptions.show', compact('prescription'));
    }

    /**
     * AJAX endpoint used by the success popup to correct medicine names before printing.
     */
    public function fixItems(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'updates' => 'required|array',
            'updates.*.item_id' => 'required|integer',
            'updates.*.field' => 'required|string|in:drug_name,instruction_note,dosage',
            'updates.*.value' => 'nullable|string|max:255',
        ]);

        foreach ($data['updates'] as $u) {
            \App\Models\PrescriptionItem::where('id', $u['item_id'])
                ->where('prescription_id', $prescription->id)
                ->update([$u['field'] => $u['value']]);
        }

        return response()->json(['success' => true]);
    }

    public function markPrinted(Prescription $prescription)
    {
        $prescription->update(['is_printed' => true]);
        return response()->json(['success' => true]);
    }

    /**
     * Return the same Blade layout used by DomPDF as clean HTML for the local
     * Electron Print Server. Printing HTML keeps text sharp on a 203-DPI
     * thermal printer and avoids PDF rasterisation/anti-aliasing blur.
     */
    public function directPrintHtml(Prescription $prescription)
    {
        $prescription->load([
            'patient', 'doctor',
            'prescriptionPatients.patient',
            'prescriptionPatients.items',
            'prescriptionPatients.radiologies',
            'prescriptionPatients.doctor',
        ]);

        $barcodeSvg = $this->buildBarcodeSvg($prescription->prescription_number);
        $directPrintServer = true;

        return response()
            ->view('prescriptions.print', compact('prescription', 'barcodeSvg', 'directPrintServer'))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store');
    }

    public function print(Prescription $prescription)
    {
        $prescription->load([
            'patient', 'doctor',
            'prescriptionPatients.patient',
            'prescriptionPatients.items',
            'prescriptionPatients.radiologies',
            'prescriptionPatients.doctor',
        ]);
        $prescription->update(['is_printed' => true]);

        // Decimal values are cast to float in the Eloquent models.
        $barcodeSvg = $this->buildBarcodeSvg($prescription->prescription_number);

        $pdf = Pdf::loadView('prescriptions.print', compact('prescription', 'barcodeSvg'))
            // 72mm printable page width for XP-80T (72 / 25.4 * 72 = 204.094pt)
            ->setPaper([0, 0, 204.094, 600], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        return $pdf->stream("prescription-{$prescription->prescription_number}.pdf");
    }

    protected function buildBarcodeSvg(string $text): string
    {
        $bits = '1101';
        foreach (str_split($text) as $ch) {
            $bits .= str_pad(decbin(ord($ch)), 8, '0', STR_PAD_LEFT);
        }
        $bits .= '1101';

        $barWidth = 1.4;
        $height = 46;
        $x = 0;
        $rects = '';
        foreach (str_split($bits) as $bit) {
            if ($bit === '1') {
                $rects .= '<rect x="'.$x.'" y="0" width="'.$barWidth.'" height="'.$height.'" fill="#000"/>';
            }
            $x += $barWidth;
        }

        return '<svg viewBox="0 0 '.$x.' '.$height.'" xmlns="http://www.w3.org/2000/svg" width="'.$x.'" height="'.$height.'">'.$rects.'</svg>';
    }

    public function destroy(Prescription $prescription)
    {
        $prescription->delete();
        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription cancelled.');
    }

    public function addPatient(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'patient_id' => 'required|integer',
            'doctor_id' => 'nullable|integer',
            'doctor_fee' => 'nullable|numeric|min:0',
            'diagnosis' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.strength' => 'nullable|string|max:100',
            'items.*.timing' => 'required|in:OD,BD,BID,TDS,QID',
            'items.*.duration_days' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric',
            'radiologies' => 'nullable|array',
        ]);

        $this->service->addPatient($prescription, $data);
        $prescription->refresh();
        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Patient added to prescription.');
    }
}
