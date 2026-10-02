<?php

namespace App\Services;

use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrescriptionEditor
{
    public function __construct(private PrescriptionStockLedger $stock) {}

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['prescription' => $message]);
    }

    public function snapshot(int $id): array
    {
        $patients = DB::table('prescription_patients')->where('prescription_id', $id)->orderBy('id')->get();
        return [
            'prescription' => (array) DB::table('prescriptions')->where('id', $id)->first(),
            'patients' => $patients->map(fn ($v) => (array) $v)->all(),
            'items' => DB::table('prescription_items')->where('prescription_id', $id)->orderBy('id')
                ->get()->map(fn ($v) => (array) $v)->all(),
            'allocations' => DB::table('prescription_stock_allocations')
                ->whereIn('prescription_item_id', DB::table('prescription_items')->select('id')->where('prescription_id', $id))
                ->orderBy('id')->get()->map(fn ($v) => (array) $v)->all(),
            'radiologies' => DB::table('prescription_patient_radiology')
                ->whereIn('prescription_patient_id', $patients->pluck('id'))->orderBy('id')
                ->get()->map(fn ($v) => (array) $v)->all(),
        ];
    }

    public function revision(array $snapshot): string
    {
        return hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR));
    }

    public function assertEditable(Prescription $rx): void
    {
        if ($rx->sale_status !== 'unsold' || $rx->status === 'cancelled'
            || DB::table('sales')->where('prescription_id', $rx->id)->exists()
            || DB::table('sale_items')->whereIn('prescription_item_id',
                DB::table('prescription_items')->select('id')->where('prescription_id', $rx->id))->exists()) {
            $this->fail('Sold, partially sold, cancelled, or sale-linked prescriptions cannot be edited.');
        }
    }

    public function update(Prescription $prescription, array $data, ?int $userId): Prescription
    {
        return DB::transaction(function () use ($prescription, $data, $userId) {
            $rx = Prescription::whereKey($prescription->id)->lockForUpdate()->firstOrFail();
            $this->assertEditable($rx);
            $before = $this->snapshot($rx->id);
            if (!hash_equals($this->revision($before), $data['revision'])) {
                $this->fail('This prescription changed after you opened it. Open a fresh Edit page, review the latest data, and reapply your changes.');
            }
            $oldPatients = collect($before['patients'])->keyBy('id');
            $oldItems = collect($before['items'])->keyBy('id');
            $oldRads = collect($before['radiologies'])->keyBy('id');
            $seenPatients = $seenItems = $seenRads = [];
            foreach ($data['patients'] as $p) {
                $ppid = (int) ($p['id'] ?? 0);
                if ($ppid && (!$oldPatients->has($ppid) || isset($seenPatients[$ppid]))) {
                    $this->fail('Invalid or duplicate prescription patient ID.');
                }
                if ($ppid) $seenPatients[$ppid] = true;
                foreach ($p['items'] as $i) {
                    $id = (int) ($i['id'] ?? 0);
                    if ($id && (!$oldItems->has($id) || !$ppid
                        || (int) $oldItems[$id]['prescription_patient_id'] !== $ppid || isset($seenItems[$id]))) {
                        $this->fail('Invalid, duplicate, or cross-patient medicine ID.');
                    }
                    if ($id) $seenItems[$id] = true;
                }
                foreach (($p['radiologies'] ?? []) as $r) {
                    $id = (int) ($r['id'] ?? 0);
                    if ($id && (!$oldRads->has($id) || !$ppid
                        || (int) $oldRads[$id]['prescription_patient_id'] !== $ppid || isset($seenRads[$id]))) {
                        $this->fail('Invalid, duplicate, or cross-patient radiology ID.');
                    }
                    if ($id) $seenRads[$id] = true;
                }
            }
            // Never silently discard older orphan/ungrouped medicines.
            foreach ($oldItems as $i) {
                if (!$oldPatients->has($i['prescription_patient_id'])) {
                    $this->fail('This prescription contains ungrouped legacy items. Administrator review is required before editing.');
                }
            }
            // Remove omitted rows explicitly, returning only provable stock allocations.
            foreach ($oldItems as $id => $i) {
                if (!isset($seenItems[$id])) {
                    $this->stock->change((object) $i, $i['product_id'], 0);
                    DB::table('prescription_items')->where('id', $id)->delete();
                }
            }
            foreach ($oldRads as $id => $r) {
                if (!isset($seenRads[$id])) DB::table('prescription_patient_radiology')->where('id', $id)->delete();
            }
            foreach ($oldPatients as $id => $p) {
                if (!isset($seenPatients[$id])) DB::table('prescription_patients')->where('id', $id)->delete();
            }
            // Release all decreases/replacements before any increase. This permits
            // stock-neutral transfers between patients/items even when shelves are empty.
            foreach ($data['patients'] as $p) {
                foreach ($p['items'] as $i) {
                    $id = (int) ($i['id'] ?? 0);
                    if (!$id) continue;
                    $old = $oldItems[$id];
                    if ((int) $old['product_id'] !== (int) $i['product_id']) {
                        $this->stock->change((object) $old, (int) $i['product_id'], 0);
                        DB::table('prescription_items')->where('id', $id)->update(['stock_deducted' => 0, 'batch_id' => null]);
                        $old['stock_deducted'] = 0;
                        $old['batch_id'] = null;
                        $oldItems[$id] = $old;
                    } elseif (PrescriptionStockLedger::units($i['quantity']) < PrescriptionStockLedger::units($old['quantity'])) {
                        $this->stock->change((object) $old, (int) $i['product_id'], $i['quantity']);
                        DB::table('prescription_items')->where('id', $id)->update(['quantity' => $i['quantity']]);
                        $old['quantity'] = $i['quantity'];
                        $oldItems[$id] = $old;
                    }
                }
            }
            $medicine = $doctor = $radiology = $discount = 0;
            $primary = null;
            foreach ($data['patients'] as $p) {
                $ppid = (int) ($p['id'] ?? 0);
                $patient = DB::table('patients')->where('id', $p['patient_id'])->first();
                if (!$patient) $this->fail('A selected patient no longer exists.');
                $old = $ppid ? $oldPatients[$ppid] : null;
                $snapshot = $old && (int) $old['patient_id'] === (int) $p['patient_id']
                    ? array_intersect_key($old, array_flip(['patient_name', 'patient_age', 'patient_phone', 'patient_code']))
                    : ['patient_name' => $patient->name, 'patient_age' => $patient->age,
                        'patient_phone' => $patient->phone, 'patient_code' => $patient->patient_code];
                $pp = $snapshot + [
                    'prescription_id' => $rx->id, 'patient_id' => $patient->id,
                    'doctor_id' => $p['doctor_id'] ?? null,
                    'diagnosis' => $p['diagnosis'] ?? null, 'precautions' => $p['precautions'] ?? null,
                    'next_visit' => $p['next_visit'] ?? null,
                    'doctor_fee' => $p['doctor_fee'], 'discount' => $p['discount'], 'updated_at' => now(),
                ];
                if ($ppid) DB::table('prescription_patients')->where('id', $ppid)->update($pp);
                else $ppid = DB::table('prescription_patients')->insertGetId($pp + ['created_at' => now()]);
                // Relationship uses orderBy(id); retain the oldest remaining patient as primary.
                if ($primary === null || $ppid < $primary['id']) $primary = $pp + ['id' => $ppid];
                $pm = $pr = 0;
                foreach ($p['items'] as $i) {
                    $id = (int) ($i['id'] ?? 0);
                    $oldItem = $id ? $oldItems[$id] : null;
                    $product = DB::table('products')->where('id', $i['product_id'])->first();
                    if (!$product) $this->fail('A selected product no longer exists.');
                    $same = $oldItem && (int) $oldItem['product_id'] === (int) $product->id;
                    if (!$same && (!$product->is_active || $product->deleted_at !== null)) {
                        $this->fail('New medicine selections must be active products.');
                    }
                    if ($id) $this->stock->change((object) $oldItem, $product->id, $i['quantity']);
                    $gross = (int) round(PrescriptionStockLedger::units($i['quantity']) * PrescriptionStockLedger::units($i['unit_price']) / 100);
                    $lineDiscount = PrescriptionStockLedger::units($i['discount']);
                    if ($lineDiscount > $gross) $this->fail('Medicine discount cannot exceed the line amount.');
                    $line = $gross - $lineDiscount;
                    if ($line > 999999999999) $this->fail('Medicine line exceeds the database amount limit.');
                    $row = [
                        'prescription_id' => $rx->id, 'prescription_patient_id' => $ppid,
                        'product_id' => $product->id, 'drug_name' => $i['drug_name'],
                        'form_type' => $same ? $oldItem['form_type'] : $product->form_type,
                        'strength' => $i['strength'] ?? null,
                        'timing' => $i['timing'], 'timing_multiplier' => ['OD'=>1,'BD'=>2,'BID'=>2,'TDS'=>3,'QID'=>4][$i['timing']],
                        'duration_days' => $i['duration_days'], 'quantity' => $i['quantity'],
                        'meal_relation' => $i['meal_relation'] ?? null,
                        'instruction' => $i['instruction'] ?? null, 'instruction_note' => $i['instruction_note'] ?? null,
                        'unit_price' => $i['unit_price'], 'discount' => $i['discount'],
                        'total' => $line / 100, 'updated_at' => now(),
                    ];
                    // times_of_day is not overwritten by this editor.
                    if ($id) DB::table('prescription_items')->where('id', $id)->update($row);
                    else DB::table('prescription_items')->insert($row + ['stock_deducted' => 0, 'created_at' => now()]);
                    $pm += $line;
                }
                foreach (($p['radiologies'] ?? []) as $r) {
                    $price = PrescriptionStockLedger::units($r['price']);
                    $row = [
                        'prescription_patient_id' => $ppid,
                        'radiology_test_id' => $r['radiology_test_id'] ?? null,
                        'test_name' => $r['test_name'], 'price' => $price / 100,
                        'notes' => $r['notes'] ?? null, 'updated_at' => now(),
                    ];
                    if (!empty($r['id'])) DB::table('prescription_patient_radiology')->where('id', $r['id'])->update($row);
                    else DB::table('prescription_patient_radiology')->insert($row + ['created_at' => now()]);
                    $pr += $price;
                }
                $pd = PrescriptionStockLedger::units($p['doctor_fee']);
                $pdiscount = PrescriptionStockLedger::units($p['discount']);
                if (max($pm, $pr, $pm + $pr + $pd) > 999999999999) $this->fail('Patient total exceeds the database amount limit.');
                if ($pdiscount > $pm + $pr + $pd) $this->fail('Patient discount cannot exceed that patient subtotal.');
                DB::table('prescription_patients')->where('id', $ppid)->update([
                    'medicine_cost' => $pm / 100, 'radiology_cost' => $pr / 100,
                    'total' => ($pm + $pr + $pd - $pdiscount) / 100,
                ]);
                $medicine += $pm; $doctor += $pd; $radiology += $pr; $discount += $pdiscount;
            }
            $tax = PrescriptionStockLedger::units($data['tax']);
            $total = $medicine + $doctor + $radiology - $discount + $tax;
            $paid = PrescriptionStockLedger::units($rx->paid_amount);
            if ($total < $paid) $this->fail('New total is below the amount already paid. Use the existing refund/payment workflow first.');
            if (max($medicine, $doctor, $radiology, $discount, $total) > 999999999999) $this->fail('Prescription total exceeds the database amount limit.');
            $header = array_intersect_key($data, array_flip([
                'prescription_date', 'diagnosis', 'lab_workup', 'precautions', 'physiotherapy', 'notes', 'next_visit',
            ]));
            $header += array_intersect_key($primary, array_flip([
                'patient_id', 'doctor_id', 'patient_name', 'patient_age', 'patient_phone', 'patient_code',
            ]));
            DB::table('prescriptions')->where('id', $rx->id)->update($header + [
                'medicine_cost' => $medicine / 100, 'doctor_fee' => $doctor / 100,
                'radiology_cost' => $radiology / 100, 'discount' => $discount / 100, 'tax' => $tax / 100,
                'total_fee' => $total / 100, 'due_amount' => ($total - $paid) / 100,
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'is_printed' => 0, 'updated_at' => now(),
            ]);
            $this->stock->deductPending($rx);
            DB::table('prescription_edit_audits')->insert([
                'prescription_id' => $rx->id, 'user_id' => $userId,
                'before_data' => json_encode($before, JSON_THROW_ON_ERROR),
                'after_data' => json_encode($this->snapshot($rx->id), JSON_THROW_ON_ERROR), 'created_at' => now(),
            ]);
            return $rx->fresh();
        }, 3);
    }
}
