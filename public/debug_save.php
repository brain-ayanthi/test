<?php
/**
 * SAVE DEBUGGER - http://127.0.0.1:8000/debug_save.php
 *
 * 1. Displays what columns prescription_items actually has
 * 2. Tests inserting a row with qty=9, total=45
 * 3. Reads it back to confirm
 * 4. Cleans up
 *
 * Run this in your browser and send me the output.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "  CLINICMS SAVE DEBUGGER\n";
echo "========================================\n\n";

// 1. Table structure
echo "### 1. prescription_items TABLE STRUCTURE ###\n";
$cols = DB::select("SHOW COLUMNS FROM prescription_items");
$hasQty = false; $hasTotal = false; $hasPpId = false; $hasPrice = false;
foreach ($cols as $c) {
    echo sprintf("  %-25s %-20s Null=%-3s Default=%s\n",
        $c->Field, $c->Type, $c->Null, $c->Default ?? 'NULL');
    if ($c->Field === 'quantity') $hasQty = true;
    if ($c->Field === 'total') $hasTotal = true;
    if ($c->Field === 'prescription_patient_id') $hasPpId = true;
    if ($c->Field === 'unit_price') $hasPrice = true;
}
echo "\n";

if (!$hasPpId) {
    echo ">>> FATAL: prescription_patient_id column MISSING!\n";
    echo ">>> Run fix_prescription_items_columns.sql first.\n";
    exit;
}
if (!$hasQty || !$hasTotal) {
    echo ">>> FATAL: quantity/total columns missing!\n";
    exit;
}

// 2. Get a patient and product
$patient = DB::table('patients')->first();
$product = DB::table('products')->first();
if (!$patient || !$product) {
    echo "Need at least 1 patient and 1 product.\n";
    exit;
}
echo "### 2. TEST DATA ###\n";
echo "  Patient: #{$patient->id} {$patient->name}\n";
echo "  Product: #{$product->id} {$product->name} (selling_price={$product->selling_price})\n\n";

// 3. Try direct insert
echo "### 3. ATTEMPTING DIRECT INSERT ###\n";
DB::beginTransaction();
try {
    $now = now();

    DB::table('prescriptions')->insert([
        'prescription_number' => 'RX-DEBUG-' . time(),
        'prescription_date' => $now,
        'status' => 'active',
        'sale_status' => 'unsold',
        'patient_id' => $patient->id,
        'created_at' => $now, 'updated_at' => $now,
    ]);
    $rxId = DB::getPdo()->lastInsertId();
    echo "  Prescription #$rxId created\n";

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
        'created_at' => $now, 'updated_at' => $now,
    ]);
    $ppId = DB::getPdo()->lastInsertId();
    echo "  PrescriptionPatient #$ppId created\n";

    // THE ACTUAL TEST: qty=9, total=45
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
    echo "  PrescriptionItem #$itemId inserted\n\n";

    // 4. Read back
    $saved = DB::table('prescription_items')->where('id', $itemId)->first();
    echo "### 4. READ BACK FROM DATABASE ###\n";
    echo "  id:                    {$saved->id}\n";
    echo "  drug_name:             {$saved->drug_name}\n";
    echo "  prescription_id:       {$saved->prescription_id}\n";
    echo "  prescription_patient_id: {$saved->prescription_patient_id}\n";
    echo "  product_id:            {$saved->product_id}\n";
    echo "  timing:                {$saved->timing}\n";
    echo "  duration_days:         {$saved->duration_days}\n";
    echo "  quantity:              >>> {$saved->quantity} <<<\n";
    echo "  unit_price:            >>> {$saved->unit_price} <<<\n";
    echo "  discount:              {$saved->discount}\n";
    echo "  total:                 >>> {$saved->total} <<<\n";
    echo "\n";

    if ((float)$saved->quantity == 9.0 && (float)$saved->total == 45.0) {
        echo ">>> SUCCESS: Database SAVES quantity and total correctly!\n";
        echo ">>> The issue is in the BROWSER/JS payload or old cached PHP files.\n";
    } else {
        echo ">>> FAIL: Database is NOT saving values (got qty={$saved->quantity}, total={$saved->total})\n";
    }

    // Cleanup
    DB::table('prescription_items')->where('id', $itemId)->delete();
    DB::table('prescription_patients')->where('id', $ppId)->delete();
    DB::table('prescriptions')->where('id', $rxId)->delete();
    DB::commit();
    echo "\n(Test data cleaned up)\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n>>> EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n========================================\n";
echo "Next steps:\n";
echo "1. If SUCCESS above -> the database works. Do these:\n";
echo "   a. Delete ALL files in storage/framework/views/\n";
echo "   b. Delete ALL files in bootstrap/cache/\n";
echo "   c. Hard refresh browser (Ctrl+Shift+Delete)\n";
echo "   d. Try saving a prescription again\n";
echo "2. If FAIL -> copy this entire output and send it to me\n";
echo "========================================\n";
