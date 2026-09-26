<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\DrugTypeController;
use App\Http\Controllers\Inventory\UnitController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReadyTreatmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/



// Diagnostic route (temporary - delete after fixing)
Route::get('/_debug/db', function () {
    header('Content-Type: text/plain; charset=utf-8');
    $out = "=== ClinicMS DB Diagnostic ===\n\n";

    // Columns
    $out .= "1. prescription_items columns:\n";
    try {
        $cols = Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM prescription_items");
        $has = ['qty'=>false,'total'=>false,'ppid'=>false,'price'=>false];
        foreach ($cols as $c) {
            $out .= "   - {$c->Field} ({$c->Type})\n";
            if ($c->Field==='quantity') $has['qty']=true;
            if ($c->Field==='total') $has['total']=true;
            if ($c->Field==='prescription_patient_id') $has['ppid']=true;
            if ($c->Field==='unit_price') $has['price']=true;
        }
        $out .= "\n";
        if (!$has['ppid']) { $out .= ">>> MISSING: prescription_patient_id column! Run fix_prescription_items_columns.sql\n"; return response($out, 200)->header('Content-Type','text/plain'); }
        if (!$has['qty'] || !$has['total']) { $out .= ">>> MISSING: quantity/total columns!\n"; return response($out, 200)->header('Content-Type','text/plain'); }
    } catch (\Throwable $e) {
        $out .= "ERROR: ".$e->getMessage()."\n";
        return response($out, 200)->header('Content-Type','text/plain');
    }

    $patient = Illuminate\Support\Facades\DB::table('patients')->first();
    $product = Illuminate\Support\Facades\DB::table('products')->first();
    if (!$patient || !$product) { $out .= "Need patient+product.\n"; return response($out,200)->header('Content-Type','text/plain'); }
    $out .= "2. Patient #{$patient->id}, Product #{$product->id}\n\n";

    Illuminate\Support\Facades\DB::beginTransaction();
    try {
        $now = now();
        Illuminate\Support\Facades\DB::table('prescriptions')->insert([
            'prescription_number'=>'RX-DBG-'.time(), 'prescription_date'=>$now,
            'status'=>'active','sale_status'=>'unsold','patient_id'=>$patient->id,
            'created_at'=>$now,'updated_at'=>$now,
        ]);
        $rxId = Illuminate\Support\Facades\DB::getPdo()->lastInsertId();
        Illuminate\Support\Facades\DB::table('prescription_patients')->insert([
            'prescription_id'=>$rxId,'patient_id'=>$patient->id,
            'patient_name'=>$patient->name,'patient_age'=>$patient->age,
            'patient_phone'=>$patient->phone,'patient_code'=>$patient->patient_code,
            'medicine_cost'=>45,'total'=>45,'created_at'=>$now,'updated_at'=>$now,
        ]);
        $ppId = Illuminate\Support\Facades\DB::getPdo()->lastInsertId();
        Illuminate\Support\Facades\DB::table('prescription_items')->insert([
            'prescription_id'=>$rxId,'prescription_patient_id'=>$ppId,
            'product_id'=>$product->id,'drug_name'=>$product->name,
            'form_type'=>$product->form_type,'strength'=>$product->strength,
            'timing'=>'TDS','timing_multiplier'=>3,'duration_days'=>3,
            'quantity'=>9,'unit_price'=>5.00,'discount'=>0,'total'=>45.00,
            'stock_deducted'=>0,'created_at'=>$now,'updated_at'=>$now,
        ]);
        $itemId = Illuminate\Support\Facades\DB::getPdo()->lastInsertId();
        $saved = Illuminate\Support\Facades\DB::table('prescription_items')->where('id',$itemId)->first();
        $out .= "3. SAVED ITEM:\n";
        $out .= "   quantity = '{$saved->quantity}'\n";
        $out .= "   unit_price = '{$saved->unit_price}'\n";
        $out .= "   total = '{$saved->total}'\n";
        $out .= "   prescription_patient_id = '{$saved->prescription_patient_id}'\n\n";
        if ((float)$saved->quantity == 9.0 && (float)$saved->total == 45.0) {
            $out .= ">>> SUCCESS: Database saves values correctly.\n";
            $out .= ">>> Issue is in browser/cache. Delete storage/framework/views/* and bootstrap/cache/*.php then Ctrl+F5.\n";
        } else {
            $out .= ">>> FAIL: Database not storing qty/total.\n";
        }
        Illuminate\Support\Facades\DB::table('prescription_items')->where('id',$itemId)->delete();
        Illuminate\Support\Facades\DB::table('prescription_patients')->where('id',$ppId)->delete();
        Illuminate\Support\Facades\DB::table('prescriptions')->where('id',$rxId)->delete();
        Illuminate\Support\Facades\DB::commit();
        $out .= "\n(Test data cleaned up)\n";
    } catch (\Throwable $e) {
        Illuminate\Support\Facades\DB::rollBack();
        $out .= "EXCEPTION: ".$e->getMessage()."\n";
        $out .= $e->getFile().":".$e->getLine()."\n";
    }
    return response($out, 200)->header('Content-Type','text/plain');
})->middleware('auth');

Route::get('/', function () {
    return redirect('/login');
});

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Patients
    Route::resource('patients', PatientController::class);
    Route::get('/api/patients/search', [PatientController::class, 'searchJson'])->name('patients.search');

    // Doctors
    Route::resource('doctors', DoctorController::class)->except(['create', 'show', 'edit']);

    // Prescriptions
    Route::resource('prescriptions', PrescriptionController::class);
    Route::post('/prescriptions/{prescription}/add-patient', [PrescriptionController::class, 'addPatient'])->name('prescriptions.add-patient');
    Route::post('/prescriptions/{prescription}/fix-items', [PrescriptionController::class, 'fixItems'])->name('prescriptions.fix-items');
    Route::get('/prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    Route::get('/prescriptions/{prescription}/direct-print-html', [PrescriptionController::class, 'directPrintHtml'])->name('prescriptions.direct-print-html');
    Route::post('/prescriptions/{prescription}/mark-printed', [PrescriptionController::class, 'markPrinted'])->name('prescriptions.mark-printed');
    Route::post('/prescriptions/{prescription}/switch-patient', [PrescriptionController::class, 'switchPatient'])->name('prescriptions.switch-patient');

    // Ready Treatments (templates)
    Route::get('/treatments', [ReadyTreatmentController::class, 'index'])->name('treatments.index');
    Route::get('/treatments/create', [ReadyTreatmentController::class, 'create'])->name('treatments.create');
    Route::post('/treatments', [ReadyTreatmentController::class, 'store'])->name('treatments.store');
    Route::get('/treatments/{treatment}/edit', [ReadyTreatmentController::class, 'edit'])->name('treatments.edit');
    Route::put('/treatments/{treatment}', [ReadyTreatmentController::class, 'update'])->name('treatments.update');
    Route::delete('/treatments/{treatment}', [ReadyTreatmentController::class, 'destroy'])->name('treatments.destroy');
    Route::get('/treatments/{treatment}/data', [ReadyTreatmentController::class, 'show'])->name('treatments.show.data');

    // Products / Inventory
    Route::get('/products/opening-stock/import', [ProductController::class, 'openingStockImportForm'])->name('products.opening-stock.form');
    Route::post('/products/opening-stock/import', [ProductController::class, 'importOpeningStock'])->name('products.opening-stock.import');
    Route::get('/products/opening-stock/template', [ProductController::class, 'openingStockTemplate'])->name('products.opening-stock.template');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);
    Route::resource('drug-types', DrugTypeController::class)->except(['create', 'show', 'edit']);

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::resource('vendors', VendorController::class)->except(['create', 'show', 'edit']);
    Route::get('/vendor-payments', [VendorPaymentController::class, 'index'])->name('vendor-payments.index');
    Route::get('/vendor-payments/{vendor}', [VendorPaymentController::class, 'show'])->name('vendor-payments.show');
    Route::post('/vendor-payments', [VendorPaymentController::class, 'store'])->name('vendor-payments.store');
    Route::delete('/vendor-payments/{vendorPayment}', [VendorPaymentController::class, 'destroy'])->name('vendor-payments.destroy');

    // Sales / POS
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
    Route::post('/sales/pos', [SaleController::class, 'storePos'])->name('sales.pos.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/print', [SaleController::class, 'printInvoice'])->name('sales.print');
    Route::post('/prescriptions/{prescription}/convert-sale', [SaleController::class, 'convertFromPrescription'])->name('prescriptions.convert-sale');

    // Expenses
    Route::get('/expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/expense-categories', [ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::put('/expense-categories/{expenseCategory}', [ExpenseController::class, 'updateCategory'])->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    // Cash / Store Management
    Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('/cash/accounts', [CashController::class, 'storeAccount'])->name('cash.accounts.store');
    Route::post('/cash/topup', [CashController::class, 'topUp'])->name('cash.topup');
    Route::post('/cash/transfer', [CashController::class, 'transfer'])->name('cash.transfer');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/product-sales', [ReportController::class, 'productSales'])->name('reports.product-sales');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/reports/expiry', [ReportController::class, 'expiry'])->name('reports.expiry');
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
    Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('/reports/doctors', [ReportController::class, 'doctorWise'])->name('reports.doctor-wise');
    Route::get('/reports/radiology', [ReportController::class, 'radiology'])->name('reports.radiology');

    // Users & Roles
    Route::resource('users', UserController::class);
});
