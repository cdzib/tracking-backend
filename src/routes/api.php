<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GpsController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GeofenceController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\RouteController;
// Endpoints públicos (sin autenticación)

Route::post('/gps/device', [GpsController::class, 'store']);
Route::post('/gps/update', [GpsController::class, 'receiveUpdate'])
    ->middleware('throttle:100,1')
    ->name('gps.update');

Route::get('/gps/device/{imei}/status', [GpsController::class, 'getDeviceStatus']);

// Endpoints privados (requieren autenticación)
Route::middleware('auth:api-passengers')->prefix('tracking')->group(function () {
    // Ubicaciones
    Route::get('/vehicles/{vehicleId}/current-location', 
        [TrackingController::class, 'currentLocation']);
    Route::get('/vehicles/{vehicleId}/route-history', 
        [TrackingController::class, 'routeHistory']);
    Route::get('/vehicles/{vehicleId}/trips', 
        [TrackingController::class, 'getTrips']);
    Route::get('/vehicles/{vehicleId}/stops', 
        [TrackingController::class, 'getStops']);
    Route::get('/vehicles/all-locations', 
        [TrackingController::class, 'allVehiclesLocations']);

    // Eventos
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/summary', [EventController::class, 'summary']);
    Route::post('/events/{eventId}/acknowledge', [EventController::class, 'acknowledge']);

    // Geofences
    Route::apiResource('geofences', GeofenceController::class);
    Route::post('/geofences/{id}/test', [GeofenceController::class, 'testLocation']);

    // Comandos
    Route::post('/devices/{deviceId}/send-command', [CommandController::class, 'sendCommand']);
    Route::get('/devices/{deviceId}/commands', [CommandController::class, 'listCommands']);
    Route::get('/devices/{deviceId}/health', [TrackingController::class, 'deviceHealth']);

    // Estadísticas
    Route::get('/statistics/distance', [StatisticsController::class, 'distance']);
    Route::get('/statistics/speed', [StatisticsController::class, 'speed']);
});

// Rutas de rutas, paradas y horarios
Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{id}/stops', [RouteController::class, 'stops']);
Route::get('/routes/{id}/schedules', [RouteController::class, 'schedules']);
require __DIR__.'/api/bookings.php';
require __DIR__.'/api/trip.php';
require __DIR__.'/api/notifications.php';
require __DIR__.'/api/presence.php';
require __DIR__.'/api/data_sync.php';
