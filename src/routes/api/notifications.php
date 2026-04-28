<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

// Notificación privada a usuario
Route::post('/notify-user', [NotificationController::class, 'notifyUser']);
