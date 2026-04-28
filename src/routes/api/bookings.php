<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\Auth\PassengerAuthController;

use App\Http\Controllers\Api\PassengerReservationController;

Route::middleware('auth:api-passengers')->group(function () {
    // Reservas del pasajero
    Route::post('/reservations', [PassengerReservationController::class, 'store']);
    Route::get('/trips', [PassengerReservationController::class, 'myTrips']);
    Route::get('/trips/recent', [PassengerReservationController::class, 'recentTrips']);
    Route::get('/trips/next', [PassengerReservationController::class, 'nextTrip']);
    Route::get('/trips/history', [PassengerReservationController::class, 'history']);
    Route::get('/reservations/{id}', [PassengerReservationController::class, 'show']);
    Route::patch('/reservations/{id}/cancel', [PassengerReservationController::class, 'cancel']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/occupied-seats', [BookingController::class, 'occupiedSeats']);
    Route::get('/bookings/available-trips', [BookingController::class, 'availableTrips']);
    Route::patch('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
});
Route::post('/auth/passenger/register', [PassengerAuthController::class, 'register']);
Route::post('/auth/passenger/login', [PassengerAuthController::class, 'login']);
