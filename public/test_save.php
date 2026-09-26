<?php
/**
 * DIRECT SAVE TEST - http://127.0.0.1:8000/test_save.php
 * This bypasses everything and writes a test prescription directly
 * to prove whether the database columns accept the data.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIRECT DATABASE SAVE TEST ===\n\n";

// 1. Check columns exist
echo "1. Checking prescription_items columns:\n";
$cols = DB::select("SHOW COLUMNS FROM prescription_items");
$hasPpId = false;
foreach ($cols as $c) {
    echo "   - {$c->Field} ({$c->Type}) Null={$c->Null}\n";
    if ($c->Field === 'prescription_patient_id') $hasPpId = true;
}
echo "   prescription_patient_id: " . ($hasPpId ? "EXISTS" : "MISSING!") . "\n\n";

if (!$hasPpId) {
    echo ">>> ERROR: prescription_patient_id column is MISSING.\n";
    echo ">>> Run fix_prescription_items_columns.sql in phpMyAdmin first.\n";
    exit;
}

// 2. Find or create test patient
$patient = DB::table('patients')->first();
if (!$patient) {
    echo "2. No patients found. Please create a patient first.\n";
    exit;
}
echo "2. Using patient #{$patient->id}: {$patient->name}\n";

// 3. Find a product
$product = DB::table('products')->first();
if (!$product) {
    echo "3. No products found.\n";
    exit;
}
echo "3. Using product #{$product->id}: {$product->name} (price={$product->selling_price})\n\n";

// 4. Create a test prescription
DB::beginTransaction();
try {
    $now = now();
    $rxNo = 'RX-TEST-' . time();

    DB::table('prescriptions')->insert([
        'prescription_number' => $rxNo,
        'prescription_date' => $now,
        'status' => 'active',
        'sale_status' => 'unsold',
        'patient_id' => $patient->id,
        'medicine_cost' => 0,
        'doctor_fee' => 0,
        'radiology_cost' => 0,
        'discount' => 0,
        'total_fee' => 0,
        'is_printed' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $rxId = DB::getPdo()->lastInsertId();
    echo "4. Created prescription #{$rxId} ({$rxNo})\n";

    DB::table('prescription_patients')->insert([
        'prescription_id' => $rxId,
        'patient_id' => $patient->id,
        'patient_name' => $patient->name,
        'patient_age' => $patient->age,
        'patient_phone' => $patient->phone,
        'patient_code' => $patient->patient_code,
        'doctor_fee' => 0,
        'discount' => 0,
        'medicine_cost' => 45,
        'radiology_cost' => 0,
        'total' => 45,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $ppId = DB::getPdo()->lastInsertId();
    echo "   Created prescription_patient #{$ppId}\n";

    // THE CRITICAL INSERT - quantity=9, total=45
    DB::table('prescription_items')->insert([
        'prescription_id' => $rxId,
        'prescription_patient_id' => $ppId,
        'product_id' => $product->id,
        'drug_name' => $product->name,
        'form_type' => $product->form_type,
        'strength' => $product->strength,
        'timing' => 'TDS',
        'timing_multiplier' => 3,
        'duration_days' => 3,
        'quantity' => 9,
        'unit_price' => 5.00,
        'discount' => 0,
        'total' => 45.00,
        'stock_deducted' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $itemId = DB::getPdo()->lastInsertId();
    echo "   Created prescription_item #{$itemId}\n\n";

    // 5. Read it back
    $saved = DB::table('prescription_items')->where('id', $itemId)->first();
    echo "5. READ BACK from database:\n";
    echo "   id: {$saved->id}\n";
    echo "   drug_name: {$saved->drug_name}\n";
    echo "   timing: {$saved->timing}\n";
    echo "   duration_days: {$saved->duration_days}\n";
    echo "   quantity: '{$saved->quantity}'\n";
    echo "   unit_price: '{$saved->unit_price}'\n";
    echo "   total: '{$saved->total}'\n";
    echo "   prescription_patient_id: '{$saved->prescription_patient_id}'\n\n";

    if ($saved->quantity == 9 && $saved->total == 45) {
        echo ">>> SUCCESS! Quantity and total ARE saving correctly.\n";
        echo ">>> The problem is in the JavaScript/controller, not the database.\n";
    } else {
        echo ">>> FAIL! Saved values don't match (qty={$saved->quantity}, total={$saved->total})\n";
    }

    // Clean up test data
    DB::table('prescription_items')->where('id', $itemId)->delete();
    DB::table('prescription_patients')->where('id', $ppId)->delete();
    DB::table('prescriptions')->where('id', $rxId)->delete();
    echo "\n(Test data cleaned up)\n";

    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n>>> ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
