<?php
// Include ONCE from web.php, preserving any required clinic scope middleware.
// This file already requires auth. Do NOT put it inside an Admin-only group.
\Illuminate\Support\Facades\Route::middleware('auth')->get('/reports/inventory',
    [\App\Http\Controllers\InventoryReportController::class, 'index'])->name('reports.inventory');
