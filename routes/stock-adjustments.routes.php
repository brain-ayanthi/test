<?php
// MERGE INTO routes/web.php, INSIDE your existing authenticated route group.
// Do not replace web.php. Do not put these inside an Admin-only or
// 'manage inventory' group: Doctor must be allowed too.
// If pasted into web.php, omit this file's opening <?php line.
\Illuminate\Support\Facades\Route::middleware('auth')->group(function () {
    \Illuminate\Support\Facades\Route::get('/stock-adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'index'])
        ->name('stock-adjustments.index');
    \Illuminate\Support\Facades\Route::get('/stock-adjustments/products/{product}/batches', [\App\Http\Controllers\StockAdjustmentController::class, 'batches'])
        ->whereNumber('product')->name('stock-adjustments.batches');
    \Illuminate\Support\Facades\Route::post('/stock-adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])
        ->name('stock-adjustments.store');
    // Atomic multi-row submission: up to StockAdjustmentBulkService::MAX_ITEMS rows.
    \Illuminate\Support\Facades\Route::post('/stock-adjustments/bulk', [\App\Http\Controllers\StockAdjustmentController::class, 'storeBulk'])
        ->name('stock-adjustments.bulk');
});
