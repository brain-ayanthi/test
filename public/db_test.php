<?php
/**
 * STANDALONE DATABASE DIAGNOSTIC - No Laravel needed!
 *
 * Place this file in: D:\xampp\htdocs\clinicms\public\
 * Then open: http://127.0.0.1:8000/db_test.php
 *
 * It reads .env, connects directly to MySQL, and tests an insert.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

echo "============================================\n";
echo "  CLINICMS STANDALONE DB TEST\n";
echo "============================================\n\n";

// 1. Read .env file directly
$envPath = __DIR__ . '/../.env';
echo "1. Looking for .env at: $envPath\n";
if (!file_exists($envPath)) {
    echo "   ERROR: .env file not found!\n";
    exit;
}
echo "   Found .env\n\n";

$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_DATABASE'] ?? 'clinicms';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

echo "2. Database config from .env:\n";
echo "   Host: $host:$port\n";
echo "   Database: $dbname\n";
echo "   User: $user\n";
echo "   Pass: " . (empty($pass) ? '(empty)' : '(set)') . "\n\n";

// 2. Connect to MySQL
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ]);
    echo "3. MySQL connection: SUCCESS\n\n";
} catch (PDOException $e) {
    echo "3. MySQL connection FAILED: " . $e->getMessage() . "\n";
    echo "   Check XAMPP MySQL is running and credentials in .env\n";
    exit;
}

// 3. Check prescription_items columns
echo "4. prescription_items columns:\n";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM prescription_items")->fetchAll();
    $hasQty = $hasTotal = $hasPpid = $hasPrice = false;
    foreach ($cols as $c) {
        echo "   - {$c->Field} ({$c->Type}) Null={$c->Null}\n";
        if ($c->Field === 'quantity') $hasQty = true;
        if ($c->Field === 'total') $hasTotal = true;
        if ($c->Field === 'prescription_patient_id') $hasPpid = true;
        if ($c->Field === 'unit_price') $hasPrice = true;
    }
    echo "\n";
    if (!$hasPpid) {
        echo ">>> MISSING: prescription_patient_id column!\n";
        echo ">>> This is why items don't save. Run:\n";
        echo "    ALTER TABLE prescription_items ADD COLUMN prescription_patient_id BIGINT UNSIGNED NULL;\n\n";
    }
    if (!$hasQty) echo ">>> MISSING: quantity column!\n";
    if (!$hasTotal) echo ">>> MISSING: total column!\n";
    if (!$hasPrice) echo ">>> MISSING: unit_price column!\n";
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 4. Check other tables
echo "\n5. Required tables:\n";
$tables = ['prescriptions', 'prescription_patients', 'prescription_items', 'patients', 'products', 'radiology_tests', 'prescription_patient_radiology'];
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "   $t: EXISTS ($count rows)\n";
    } catch (PDOException $e) {
        echo "   $t: MISSING!\n";
    }
}

// 5. Find patient and product
echo "\n6. Test data:\n";
try {
    $patient = $pdo->query("SELECT id, name, patient_code FROM patients LIMIT 1")->fetch();
    $product = $pdo->query("SELECT id, name, selling_price FROM products LIMIT 1")->fetch();
    if (!$patient) { echo "   No patients found!\n"; exit; }
    if (!$product) { echo "   No products found!\n"; exit; }
    echo "   Patient: #{$patient->id} {$patient->name} ({$patient->patient_code})\n";
    echo "   Product: #{$product->id} {$product->name} (Rs {$product->selling_price})\n";
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    exit;
}

// 6. DIRECT INSERT TEST
echo "\n7. Testing DIRECT INSERT (qty=9, total=45):\n";
$pdo->beginTransaction();
try {
    $now = date('Y-m-d H:i:s');
    $rxNo = 'RX-TEST-' . time();

    $pdo->exec("INSERT INTO prescriptions (prescription_number, prescription_date, status, sale_status, patient_id, created_at, updated_at)
                VALUES (" . $pdo->quote($rxNo) . ", NOW(), 'active', 'unsold', " . (int)$patient->id . ", " . $pdo->quote($now) . ", " . $pdo->quote($now) . ")");
    $rxId = $pdo->lastInsertId();
    echo "   Prescription #$rxId created\n";

    $pdo->exec("INSERT INTO prescription_patients (prescription_id, patient_id, patient_name, patient_age, patient_phone, patient_code, doctor_fee, discount, medicine_cost, radiology_cost, total, created_at, updated_at)
                VALUES ($rxId, " . (int)$patient->id . ", " . $pdo->quote($patient->name) . ", 30, '', " . $pdo->quote($patient->patient_code) . ", 0, 0, 45, 0, 45, " . $pdo->quote($now) . ", " . $pdo->quote($now) . ")");
    $ppId = $pdo->lastInsertId();
    echo "   PrescriptionPatient #$ppId created\n";

    // THE KEY TEST
    $sql = "INSERT INTO prescription_items
            (prescription_id, prescription_patient_id, product_id, drug_name, form_type, strength,
             timing, timing_multiplier, duration_days, quantity, unit_price, discount, total, stock_deducted, created_at, updated_at)
            VALUES ($rxId, $ppId, " . (int)$product->id . ", " . $pdo->quote($product->name) . ", 'tablet', '500mg',
                    'TDS', 3, 3, 9, 5.00, 0, 45.00, 0, " . $pdo->quote($now) . ", " . $pdo->quote($now) . ")";
    $pdo->exec($sql);
    $itemId = $pdo->lastInsertId();
    echo "   Item #$itemId inserted\n";

    // Read it back
    $saved = $pdo->query("SELECT * FROM prescription_items WHERE id = $itemId")->fetch();
    echo "\n8. READ BACK:\n";
    echo "   id: {$saved->id}\n";
    echo "   drug_name: {$saved->drug_name}\n";
    echo "   timing: {$saved->timing}\n";
    echo "   duration_days: {$saved->duration_days}\n";
    echo "   quantity: >>> {$saved->quantity} <<<\n";
    echo "   unit_price: >>> {$saved->unit_price} <<<\n";
    echo "   total: >>> {$saved->total} <<<\n";
    echo "   prescription_patient_id: " . ($saved->prescription_patient_id ?? 'NULL') . "\n";

    echo "\n";
    if ((float)$saved->quantity == 9.0 && (float)$saved->total == 45.0) {
        echo ">>> SUCCESS! Database SAVES qty and total correctly.\n";
        echo ">>> The problem is in PHP files or cache.\n";
    } else {
        echo ">>> FAIL! Saved qty={$saved->quantity}, total={$saved->total}\n";
    }

    // Cleanup
    $pdo->exec("DELETE FROM prescription_items WHERE id=$itemId");
    $pdo->exec("DELETE FROM prescription_patients WHERE id=$ppId");
    $pdo->exec("DELETE FROM prescriptions WHERE id=$rxId");
    $pdo->commit();
    echo "\n(Test data cleaned up)\n";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "\n>>> INSERT FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n============================================\n";
echo "Copy ALL this output and send it back.\n";
echo "============================================\n";
