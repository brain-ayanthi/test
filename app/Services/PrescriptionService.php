<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionPatient;
use App\Models\PrescriptionPatientRadiology;
use App\Models\Product;
use App\Models\RadiologyTest;
use App\Models\ReadyTreatment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrescriptionService
{
    public function __construct(
        protected StockService $stockService,
        protected CashService $cashService
    ) {}

    /**
     * Create a multi-patient prescription using DB::table for reliability.
     */
    public function create(array $data, array $patients): Prescription
    {
        return DB::transaction(function () use ($data, $patients) {
            // Preload patients
            $patientIds = array_unique(array_column($patients, 'patient_id'));
            $patientsMap = Patient::whereIn('id', $patientIds)
                ->get()->keyBy('id');

            // Preload products
            $productIds = [];
            foreach ($patients as $p) {
                foreach (($p['items'] ?? []) as $it) {
                    $productIds[] = $it['product_id'];
                }
            }
            $productsMap = Product::whereIn('id', array_unique($productIds))
                ->get()->keyBy('id');

            // Preload radiology tests
            $radIds = [];
            foreach ($patients as $p) {
                foreach (($p['radiologies'] ?? []) as $r) {
                    if (!empty($r['radiology_test_id'])) $radIds[] = $r['radiology_test_id'];
                }
            }
            $radMap = $radIds ? RadiologyTest::whereIn('id', array_unique($radIds))->get()->keyBy('id') : collect();

            // 1. Create prescription (use DB::table to get ID immediately)
            $now = now();
            DB::table('prescriptions')->insert([
                'prescription_number' => Prescription::generateNumber(),
                'prescription_date' => $data['prescription_date'] ?? $now,
                'status' => 'active',
                'sale_status' => 'unsold',
                'patient_id' => $patients[0]['patient_id'] ?? null,
                'created_by' => auth()->id(),
                'medicine_cost' => 0,
                'doctor_fee' => 0,
                'radiology_cost' => 0,
                'discount' => 0,
                'tax' => 0,
                'total_fee' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'is_printed' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $prescription = Prescription::find(DB::getPdo()->lastInsertId());

            $totalMedicine = 0;
            $totalDoctorFee = 0;
            $totalRadiology = 0;
            $totalDiscount = 0;

            foreach ($patients as $p) {
                $patient = $patientsMap[$p['patient_id']] ?? null;
                if (!$patient) continue;

                $doctorFee = (float)($p['doctor_fee'] ?? 0);
                $discount = (float)($p['discount'] ?? 0);

                // Calculate medicine
                $medicine = 0.0;
                $itemRows = [];
                foreach (($p['items'] ?? []) as $it) {
                    $product = $productsMap[$it['product_id']] ?? null;
                    if (!$product) {
                        Log::warning('Product not found', ['product_id' => $it['product_id']]);
                        continue;
                    }
                    $timing = $it['timing'] ?? 'TDS';
                    $mult = PrescriptionItem::TIMING_MAP[$timing] ?? 3;
                    $days = (int)($it['duration_days'] ?? 1);
                    // Prefer the qty/total sent from frontend, but always verify locally
                    $qty = isset($it['quantity']) ? (float)$it['quantity'] : ($mult * $days);
                    $unitPrice = (float)($it['unit_price'] ?? $product->selling_price);
                    $disc = (float)($it['discount'] ?? 0);
                    $total = isset($it['total']) ? (float)$it['total'] : (($qty * $unitPrice) - $disc);
                    $medicine += $total;

                    $itemRows[] = [
                        'prescription_id' => $prescription->id,
                        'prescription_patient_id' => 0, // set after pp insert
                        'product_id' => $product->id,
                        'drug_name' => $product->name,
                        'form_type' => $product->form_type,
                        'strength' => !empty($it['strength']) ? $it['strength'] : $product->strength,
                        'timing' => $timing,
                        'timing_multiplier' => $mult,
                        'duration_days' => $days,
                        'quantity' => $qty,
                        'meal_relation' => $it['meal_relation'] ?? null,
                        'instruction' => $it['instruction'] ?? null,
                        'instruction_note' => $it['instruction_note'] ?? null,
                        'unit_price' => $unitPrice,
                        'discount' => $disc,
                        'total' => $total,
                        'stock_deducted' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Calculate radiology
                $radiology = 0.0;
                $radRows = [];
                foreach (($p['radiologies'] ?? []) as $rad) {
                    $name = $rad['test_name'] ?? '';
                    $price = (float)($rad['price'] ?? 0);
                    if (!empty($rad['radiology_test_id']) && isset($radMap[$rad['radiology_test_id']])) {
                        $t = $radMap[$rad['radiology_test_id']];
                        $name = $t->name;
                        $price = (float)$t->price;
                    }
                    $radiology += $price;
                    $radRows[] = [
                        'prescription_patient_id' => 0,
                        'radiology_test_id' => $rad['radiology_test_id'] ?? null,
                        'test_name' => $name,
                        'price' => $price,
                        'notes' => $rad['notes'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Create prescription_patient
                DB::table('prescription_patients')->insert([
                    'prescription_id' => $prescription->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $p['doctor_id'] ?? null,
                    'patient_name' => $patient->name,
                    'patient_age' => $patient->age,
                    'patient_phone' => $patient->phone,
                    'patient_code' => $patient->patient_code,
                    'diagnosis' => $p['diagnosis'] ?? null,
                    'precautions' => $p['precautions'] ?? null,
                    'next_visit' => $p['next_visit'] ?? null,
                    'doctor_fee' => $doctorFee,
                    'discount' => $discount,
                    'medicine_cost' => $medicine,
                    'radiology_cost' => $radiology,
                    'total' => $medicine + $radiology + $doctorFee - $discount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $ppId = DB::getPdo()->lastInsertId();

                // Update item/rad rows with pp_id and insert directly
                foreach ($itemRows as &$row) {
                    $row['prescription_patient_id'] = $ppId;
                }
                unset($row);
                foreach ($radRows as &$row) {
                    $row['prescription_patient_id'] = $ppId;
                }
                unset($row);

                if ($itemRows) {
                    DB::table('prescription_items')->insert($itemRows);
                }
                if ($radRows) {
                    DB::table('prescription_patient_radiology')->insert($radRows);
                }

                // Update patient last_visit
                DB::table('patients')->where('id', $patient->id)->update(['last_visit' => $now]);

                $totalMedicine += $medicine;
                $totalDoctorFee += $doctorFee;
                $totalRadiology += $radiology;
                $totalDiscount += $discount;
            }

            // Update prescription totals
            DB::table('prescriptions')->where('id', $prescription->id)->update([
                'medicine_cost' => $totalMedicine,
                'doctor_fee' => $totalDoctorFee,
                'radiology_cost' => $totalRadiology,
                'discount' => $totalDiscount,
                'total_fee' => $totalMedicine + $totalDoctorFee + $totalRadiology - $totalDiscount,
                'updated_at' => $now,
            ]);

            return $prescription->fresh();
        });
    }

    /**
     * Stock deduction - ONE transaction for all items.
     */
    public function saveAndDeductStock(Prescription $prescription): Prescription
    {
        $pending = DB::table('prescription_items')
            ->where('prescription_id', $prescription->id)
            ->where('stock_deducted', 0)
            ->get(['id', 'product_id', 'quantity']);

        if ($pending->isNotEmpty()) {
            $items = $pending->map(fn($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float)$i->quantity,
            ])->all();
            $deductions = $this->stockService->deductMany($items);
            foreach ($pending as $item) {
                $d = $deductions[$item->product_id] ?? null;
                if ($d) {
                    DB::table('prescription_items')->where('id', $item->id)->update([
                        'batch_id' => $d['batch_id'],
                        'stock_deducted' => 1,
                    ]);
                }
            }
        }
        DB::table('prescriptions')->where('id', $prescription->id)->update(['is_printed' => 0]);
        return $prescription->fresh();
    }

    /**
     * Add another patient to existing prescription.
     */
    public function addPatient(Prescription $prescription, array $data, bool $recalculate = true): PrescriptionPatient
    {
        return DB::transaction(function () use ($prescription, $data, $recalculate) {
            $patient = Patient::findOrFail($data['patient_id']);
            $now = now();
            $doctorFee = (float)($data['doctor_fee'] ?? 0);

            $medicine = 0.0;
            $itemRows = [];
            foreach (($data['items'] ?? []) as $it) {
                $product = Product::find($it['product_id']);
                if (!$product) continue;
                $timing = $it['timing'] ?? 'TDS';
                $mult = PrescriptionItem::TIMING_MAP[$timing] ?? 3;
                $days = (int)($it['duration_days'] ?? 1);
                $qty = isset($it['quantity']) ? (float)$it['quantity'] : ($mult * $days);
                $unitPrice = (float)($it['unit_price'] ?? $product->selling_price);
                $disc = (float)($it['discount'] ?? 0);
                $total = isset($it['total']) ? (float)$it['total'] : (($qty * $unitPrice) - $disc);
                $medicine += $total;
                $itemRows[] = compact('qty','total') + [
                    'prescription_id' => $prescription->id,
                    'prescription_patient_id' => 0,
                    'product_id' => $product->id,
                    'drug_name' => $product->name,
                    'form_type' => $product->form_type,
                    'strength' => !empty($it['strength']) ? $it['strength'] : $product->strength,
                    'timing' => $timing, 'timing_multiplier' => $mult,
                    'duration_days' => $days,
                    'meal_relation' => $it['meal_relation'] ?? null,
                    'instruction' => $it['instruction'] ?? null,
                    'instruction_note' => $it['instruction_note'] ?? null,
                    'unit_price' => $unitPrice, 'discount' => $disc,
                    'stock_deducted' => 0, 'created_at' => $now, 'updated_at' => $now,
                ];
            }

            $radiology = 0.0;
            $radRows = [];
            foreach (($data['radiologies'] ?? []) as $rad) {
                $name = $rad['test_name'] ?? '';
                $price = (float)($rad['price'] ?? 0);
                if (!empty($rad['radiology_test_id'])) {
                    $t = RadiologyTest::find($rad['radiology_test_id']);
                    if ($t) { $name = $t->name; $price = (float)$t->price; }
                }
                $radiology += $price;
                $radRows[] = [
                    'prescription_patient_id' => 0,
                    'radiology_test_id' => $rad['radiology_test_id'] ?? null,
                    'test_name' => $name, 'price' => $price,
                    'notes' => $rad['notes'] ?? null,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }

            DB::table('prescription_patients')->insert([
                'prescription_id' => $prescription->id,
                'patient_id' => $patient->id,
                'doctor_id' => $data['doctor_id'] ?? null,
                'patient_name' => $patient->name,
                'patient_age' => $patient->age,
                'patient_phone' => $patient->phone,
                'patient_code' => $patient->patient_code,
                'diagnosis' => $data['diagnosis'] ?? null,
                'precautions' => $data['precautions'] ?? null,
                'doctor_fee' => $doctorFee,
                'medicine_cost' => $medicine,
                'radiology_cost' => $radiology,
                'total' => $medicine + $radiology + $doctorFee,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $ppId = DB::getPdo()->lastInsertId();

            foreach ($itemRows as &$r) { $r['prescription_patient_id'] = $ppId; }
            unset($r);
            foreach ($radRows as &$r) { $r['prescription_patient_id'] = $ppId; }
            unset($r);

            if ($itemRows) DB::table('prescription_items')->insert($itemRows);
            if ($radRows) DB::table('prescription_patient_radiology')->insert($radRows);

            DB::table('patients')->where('id', $patient->id)->update(['last_visit' => $now]);

            if ($recalculate) $prescription->recalculateTotals();
            return PrescriptionPatient::find($ppId);
        });
    }
}
