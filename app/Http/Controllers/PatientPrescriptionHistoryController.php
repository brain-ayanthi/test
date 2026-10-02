<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Read-only, patient-scoped history for the Prescription Create screen. */
class PatientPrescriptionHistoryController extends Controller
{
    public function index(Request $request, Patient $patient)
    {
        // Also keep this route inside the clinic's auth/permission middleware group.
        abort_unless($request->user(), 401);
        // Respect the existing patient policy when one is registered/discovered.
        if (Gate::getPolicyFor($patient)) {
            Gate::authorize('view', $patient);
        }
        $validated = $request->validate(['page' => 'nullable|integer|min:1|max:100000']);
        $patientId = (int) $patient->getKey();

        // Keep list and detail reads in one transaction (consistent snapshot on
        // the supplied InnoDB database's normal repeatable-read configuration).
        return DB::transaction(function () use ($patient, $patientId, $validated) {
            $query = DB::table('prescriptions as rx')
                ->leftJoin('doctors as legacy_doctor', 'legacy_doctor.id', '=', 'rx.doctor_id')
                ->where(function ($match) use ($patientId) {
                    // Includes secondary patients on a shared multi-patient prescription.
                    $match->whereExists(function ($sub) use ($patientId) {
                        $sub->selectRaw('1')->from('prescription_patients as pp')
                            ->whereColumn('pp.prescription_id', 'rx.id')->where('pp.patient_id', $patientId);
                    })->orWhere(function ($legacy) use ($patientId) {
                        // Fall back to the old primary patient field ONLY for truly legacy
                        // prescriptions with no patient blocks at all. Never infer ownership
                        // of another patient's block from an out-of-date header field.
                        $legacy->where('rx.patient_id', $patientId)->whereNotExists(function ($sub) {
                            $sub->selectRaw('1')->from('prescription_patients as any_pp')
                                ->whereColumn('any_pp.prescription_id', 'rx.id');
                        });
                    });
                })
                ->orderByDesc('rx.prescription_date')->orderByDesc('rx.id');
    
            $page = $query->paginate(10, [
                'rx.id', 'rx.prescription_number', 'rx.prescription_date', 'rx.status', 'rx.sale_status',
                'rx.diagnosis', 'rx.precautions', 'rx.next_visit', 'rx.lab_workup', 'rx.physiotherapy', 'rx.notes',
                'rx.medicine_cost', 'rx.doctor_fee', 'rx.radiology_cost', 'rx.discount', 'rx.tax', 'rx.total_fee',
                'legacy_doctor.name as legacy_doctor_name',
            ], 'page', (int) ($validated['page'] ?? 1));
            $ids = collect($page->items())->pluck('id');
    
            // Every detail query is bounded to the current page and selected patient.
            $blocks = DB::table('prescription_patients as pp')
                ->leftJoin('doctors as d', 'd.id', '=', 'pp.doctor_id')
                ->whereIn('pp.prescription_id', $ids)->where('pp.patient_id', $patientId)
                ->orderBy('pp.id')->get(['pp.*', 'd.name as doctor_name']);
            $blockIds = $blocks->pluck('id');
            $legacyIds = DB::table('prescriptions as legacy_rx')->whereIn('legacy_rx.id', $ids)
                ->where('legacy_rx.patient_id', $patientId)->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')->from('prescription_patients as existing_pp')
                        ->whereColumn('existing_pp.prescription_id', 'legacy_rx.id');
                })->pluck('legacy_rx.id');
            $items = DB::table('prescription_items')->whereIn('prescription_id', $ids)
                ->where(function ($scope) use ($blockIds, $legacyIds) {
                    $scope->whereIn('prescription_patient_id', $blockIds)
                        ->orWhere(function ($legacy) use ($legacyIds) {
                            $legacy->whereIn('prescription_id', $legacyIds)->whereNull('prescription_patient_id');
                        });
                })->orderBy('id')->get([
                    'id', 'prescription_id', 'prescription_patient_id', 'drug_name', 'form_type', 'strength',
                    'timing', 'times_of_day', 'duration_days', 'quantity', 'meal_relation', 'instruction',
                    'instruction_note', 'unit_price', 'discount', 'total',
                ]);
            $radiologies = DB::table('prescription_patient_radiology')->whereIn('prescription_patient_id', $blockIds)
                ->orderBy('id')->get(['id', 'prescription_patient_id', 'test_name', 'price', 'notes']);
    
            $data = collect($page->items())->map(function ($rx) use ($blocks, $items, $radiologies, $legacyIds) {
                $patientBlocks = $blocks->where('prescription_id', $rx->id);
                $legacy = $patientBlocks->isEmpty();
                if ($legacy && !$legacyIds->contains($rx->id)) return null;
                if ($legacy) {
                    $visits = [[
                        'id' => null, 'doctor_name' => $rx->legacy_doctor_name,
                        'diagnosis' => $rx->diagnosis, 'precautions' => $rx->precautions, 'next_visit' => $rx->next_visit,
                        'lab_workup' => $rx->lab_workup, 'physiotherapy' => $rx->physiotherapy, 'notes' => $rx->notes,
                        'medicine_cost' => (float) $rx->medicine_cost, 'doctor_fee' => (float) $rx->doctor_fee,
                        'radiology_cost' => (float) $rx->radiology_cost, 'discount' => (float) $rx->discount,
                        'tax' => (float) $rx->tax, 'total' => (float) $rx->total_fee,
                        'items' => $items->where('prescription_id', $rx->id)->whereNull('prescription_patient_id')
                            ->map(fn ($item) => $this->medicine($item))->values()->all(),
                        'radiologies' => [],
                    ]];
                } else {
                    $visits = $patientBlocks->map(function ($pp) use ($items, $radiologies) {
                        return [
                            'id' => (int) $pp->id, 'doctor_name' => $pp->doctor_name,
                            'diagnosis' => $pp->diagnosis, 'precautions' => $pp->precautions, 'next_visit' => $pp->next_visit,
                            'medicine_cost' => (float) $pp->medicine_cost, 'doctor_fee' => (float) $pp->doctor_fee,
                            'radiology_cost' => (float) $pp->radiology_cost, 'discount' => (float) $pp->discount,
                            'total' => (float) $pp->total,
                            'items' => $items->where('prescription_id', $pp->prescription_id)
                                ->where('prescription_patient_id', $pp->id)
                                ->map(fn ($item) => $this->medicine($item))->values()->all(),
                            'radiologies' => $radiologies->where('prescription_patient_id', $pp->id)->map(fn ($r) => [
                                'id' => (int) $r->id, 'test_name' => $r->test_name,
                                'price' => (float) $r->price, 'notes' => $r->notes,
                            ])->values()->all(),
                        ];
                    })->values()->all();
                }
                return [
                    'id' => (int) $rx->id, 'prescription_number' => $rx->prescription_number,
                    'prescription_date' => substr((string) $rx->prescription_date, 0, 10),
                    'status' => $rx->status, 'sale_status' => $rx->sale_status, 'legacy' => $legacy,
                    'patient_total' => round(array_sum(array_column($visits, 'total')), 2),
                    'visits' => $visits,
                ];
            })->filter()->values()->all();
    
            return response()->json([
                'patient' => ['id' => $patientId, 'name' => $patient->name, 'patient_code' => $patient->patient_code],
                'data' => $data,
                'meta' => [
                    'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(), 'total' => $page->total(),
                    'from' => $page->firstItem(), 'to' => $page->lastItem(),
                ],
            ])->header('Cache-Control', 'private, no-store, max-age=0')->header('Pragma', 'no-cache');
        });
    }

    private function medicine(object $item): array
    {
        $times = json_decode((string) ($item->times_of_day ?? ''), true);
        return [
            'id' => (int) $item->id, 'drug_name' => $item->drug_name, 'form_type' => $item->form_type,
            'strength' => $item->strength, 'timing' => $item->timing,
            'times_of_day' => is_array($times) ? $times : [],
            'duration_days' => (int) $item->duration_days, 'quantity' => (float) $item->quantity,
            'meal_relation' => $item->meal_relation, 'instruction' => $item->instruction,
            'instruction_note' => $item->instruction_note, 'unit_price' => (float) $item->unit_price,
            'discount' => (float) $item->discount, 'total' => (float) $item->total,
        ];
    }
}
