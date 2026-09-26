<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (for Patient Panel / future mobile app)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patient/{patient}/prescriptions', function (\App\Models\Patient $patient) {
        return $patient->prescriptions()->with('items')->latest()->paginate(20);
    });

    Route::get('/patient/{patient}/profile', function (\App\Models\Patient $patient) {
        return response()->json($patient);
    });
});
