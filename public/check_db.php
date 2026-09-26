<?php
/**
 * ClinicMS Database Diagnostic Tool
 * Run in browser: http://127.0.0.1:8000/check_db.php
 * Or command: php check_db.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

header('Content-Type: text/plain; charset=utf-8');

echo "=== ClinicMS Database Diagnostic ===\n\n";

// Check prescription_items columns
echo "1. prescription_items columns:\n";
try {
    $cols = DB::select("SHOW COLUMNS FROM prescription_items");
    foreach ($cols as $c) {
        echo "   - {$c->Field} ({$c->Type}) Null={$c->Null} Default={$c->Default}\n";
    }
} catch (\Throwable $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// Check if prescription_patient_id exists
echo "\n2. prescription_patient_id column: ";
$hasCol = Schema::hasColumn('prescription_items', 'prescription_patient_id');
echo $hasCol ? "EXISTS ✓\n" : "MISSING ✗ (this is why items don't save!)\n";

// Check prescription_patients table
echo "\n3. prescription_patients table: ";
echo Schema::hasTable('prescription_patients') ? "EXISTS ✓\n" : "MISSING ✗\n";

// Check radiology tables
echo "4. radiology_tests table: ";
echo Schema::hasTable('radiology_tests') ? "EXISTS ✓\n" : "MISSING ✗\n";
echo "5. prescription_patient_radiology table: ";
echo Schema::hasTable('prescription_patient_radiology') ? "EXISTS ✓\n" : "MISSING ✗\n";

// Count existing records
echo "\n6. Record counts:\n";
try { echo "   prescriptions: " . DB::table('prescriptions')->count() . "\n"; } catch(\Throwable $e) { echo "   ERROR\n"; }
try { echo "   prescription_patients: " . DB::table('prescription_patients')->count() . "\n"; } catch(\Throwable $e) { echo "   ERROR\n"; }
try { echo "   prescription_items: " . DB::table('prescription_items')->count() . "\n"; } catch(\Throwable $e) { echo "   ERROR\n"; }

// Show last 5 prescription items
echo "\n7. Last 5 prescription_items:\n";
try {
    $items = DB::table('prescription_items')->orderBy('id','desc')->take(5)->get();
    if ($items->isEmpty()) {
        echo "   (no records - items are NOT being saved!)\n";
    } else {
        foreach ($items as $it) {
            echo "   #{$it->id} rx={$it->prescription_id} pp=" . ($it->prescription_patient_id ?? 'NULL') . " drug={$it->drug_name} qty={$it->quantity} price={$it->unit_price} total={$it->total}\n";
        }
    }
} catch (\Throwable $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// Try a test insert
echo "\n8. Testing item insert (will be rolled back):\n";
DB::beginTransaction();
try {
    $pp = DB::table('prescription_patients')->first();
    if (!$pp) {
        echo "   SKIP: No prescription_patients to test with\n";
    } else {
        $testData = [
            'prescription_id' => $pp->prescription_id,
            'prescription_patient_id' => $pp->id,
            'product_id' => 1,
            'drug_name' => 'TEST MEDICINE',
            'form_type' => 'tablet',
            'strength' => '500mg',
            'timing' => 'TDS',
            'timing_multiplier' => 3,
            'duration_days' => 3,
            'quantity' => 9,
            'unit_price' => 5.00,
            'discount' => 0,
            'total' => 45.00,
            'stock_deducted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('prescription_items')->insert($testData);
        $id = DB::getPdo()->lastInsertId();
        $saved = DB::table('prescription_items')->where('id', $id)->first();
        echo "   INSERT SUCCESS ✓\n";
        echo "   Saved: qty={$saved->quantity}, total={$saved->total}\n";
        DB::table('prescription_items')->where('id', $id)->delete();
        echo "   (test record deleted)\n";
    }
} catch (\Throwable $e) {
    echo "   INSERT FAILED ✗: " . $e->getMessage() . "\n";
}
DB::rollBack();

echo "\n=== Done ===\n";
echo "\nIf prescription_patient_id is MISSING, run: fix_prescription_items_columns.sql\n";
