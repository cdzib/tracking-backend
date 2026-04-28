<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresenceController;

// Endpoint para sincronizar usuarios conectados en canal de presencia
Route::post('/presence/sync', [PresenceController::class, 'sync']);
