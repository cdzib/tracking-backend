<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataSyncController;

// Endpoint para sincronización global
Route::post('/sync/global', [DataSyncController::class, 'syncGlobal']);

// Endpoint para sincronización por viaje
Route::post('/trips/{trip}/sync', [DataSyncController::class, 'syncTrip']);
