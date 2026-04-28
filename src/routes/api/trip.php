<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripPresenceController;
use App\Http\Controllers\Api\TripChatController;
use App\Http\Controllers\Api\TripAlertController;

// Endpoint para sincronizar usuarios conectados en canal de presencia por viaje
Route::post('/trips/{trip}/presence/sync', [TripPresenceController::class, 'sync']);


Route::post('/trips/{trip}/chat', [TripChatController::class, 'send']);
Route::get('/trips/{trip}/chat', [TripChatController::class, 'index']);
Route::post('/trips/{trip}/alert', [TripAlertController::class, 'send']);