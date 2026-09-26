<?php
/**
 * ClinicMS Environment Checker
 * Run this from command line:  php check-environment.php
 * Or open in browser:  http://127.0.0.1:8000/check-environment.php
 *
 * This diagnoses "Please provide a valid cache path" and similar errors.
 */

echo "<pre style='font-family:Consolas,monospace;font-size:13px;background:#1e1e1e;color:#d4d4d4;padding:20px;'>";
echo str_repeat("=", 60) . "\n";
echo "  ClinicMS ENVIRONMENT CHECKER\n";
echo str_repeat("=", 60) . "\n\n";

$base = __DIR__;
$allOk = true;

function check($label, $condition, $hint = '') {
    global $allOk;
    if ($condition) {
        echo "  [OK]   $label\n";
    } else {
        echo "  [FAIL] $label\n";
        if ($hint) echo "         -> $hint\n";
        $allOk = false;
    }
}

// 1. Required directories
echo "--- Required Directories ---\n";
$dirs = [
    'bootstrap/cache',
    'storage',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/testing',
    'storage/app',
    'storage/app/public',
    'storage/app/private',
    'storage/logs',
];

foreach ($dirs as $dir) {
    $full = $base . '/' . $dir;
    if (!is_dir($full)) {
        @mkdir($full, 0775, true);
    }
    check($dir, is_dir($full), "Directory missing - attempted to create it");
    if (is_dir($full)) {
        check($dir . " is writable", is_writable($full), "Run: chmod -R 775 storage bootstrap/cache");
    }
}

echo "\n--- PHP Requirements ---\n";
check("PHP >= 8.1", version_compare(PHP_VERSION, '8.1.0', '>='), "Current: " . PHP_VERSION);
check("BCMath extension", extension_loaded('bcmath'));
check("Ctype extension", extension_loaded('ctype'));
check("JSON extension", extension_loaded('json'));
check("Mbstring extension", extension_loaded('mbstring'));
check("OpenSSL extension", extension_loaded('openssl'));
check("PDO extension", extension_loaded('pdo'));
check("PDO MySQL extension", extension_loaded('pdo_mysql'));
check("Tokenizer extension", extension_loaded('tokenizer'));
check("XML extension", extension_loaded('xml'));
check("Fileinfo extension", extension_loaded('fileinfo'));
check("GD extension (for barcode/images)", extension_loaded('gd'), "Optional but recommended");

echo "\n--- Key Files ---\n";
check(".env exists", file_exists($base . '/.env'), "Copy .env.example to .env");
check("vendor/autoload.php", file_exists($base . '/vendor/autoload.php'), "Run: composer install");
check("bootstrap/app.php", file_exists($base . '/bootstrap/app.php'));

echo "\n--- Cached Config (should be empty in dev) ---\n";
$cacheDir = $base . '/bootstrap/cache';
$cachedFiles = glob($cacheDir . '/*.php');
if (empty($cachedFiles)) {
    echo "  [OK]   No cached config files (good for development)\n";
} else {
    echo "  [INFO] Found cached files:\n";
    foreach ($cachedFiles as $f) {
        echo "         - " . basename($f) . " (" . date('Y-m-d H:i', filemtime($f)) . ")\n";
    }
    echo "         If you changed .env, delete these files or run:\n";
    echo "         php artisan optimize:clear\n";
}

echo "\n--- Storage Path Test ---\n";
$testFile = $base . '/storage/framework/cache/data/write_test_' . uniqid() . '.tmp';
$written = @file_put_contents($testFile, 'test');
if ($written !== false) {
    echo "  [OK]   Can write to cache/data directory\n";
    @unlink($testFile);
} else {
    echo "  [FAIL] Cannot write to cache/data - PERMISSION PROBLEM\n";
    echo "         Windows: right-click folder -> Properties -> Security\n";
    echo "         Linux:   chmod -R 775 storage bootstrap/cache\n";
    echo "         Also run: chown -R www-data:www-data storage bootstrap/cache\n";
    $allOk = false;
}

echo "\n--- Database Connection Test ---\n";
if (file_exists($base . '/.env')) {
    $env = parse_ini_file($base . '/.env');
    $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
    $dbName = $env['DB_DATABASE'] ?? 'clinicms';
    $dbUser = $env['DB_USERNAME'] ?? 'root';
    $dbPass = $env['DB_PASSWORD'] ?? '';

    echo "  Host: $dbHost\n  Database: $dbName\n  User: $dbUser\n\n";

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        echo "  [OK]   Database connection successful\n";

        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "         Found " . count($tables) . " tables\n";
        if (count($tables) < 30) {
            echo "         [WARNING] Few tables - import database/sql/clinicms_database.sql\n";
        }
    } catch (PDOException $e) {
        echo "  [FAIL] Database connection: " . $e->getMessage() . "\n";
        echo "         Check .env DB credentials and start MySQL\n";
        $allOk = false;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
if ($allOk) {
    echo "  ALL CHECKS PASSED! \xE2\x9C\x94\n";
    echo "  Run: php artisan serve\n";
} else {
    echo "  SOME CHECKS FAILED - see [FAIL] items above\n";
}
echo str_repeat("=", 60) . "\n";
echo "</pre>";
