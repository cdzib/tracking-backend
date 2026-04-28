<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log; 

Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    Log::info('[Broadcast] Intento de conexión al canal trip.' . $tripId, [
        'user_id' => $user?->id,
        'user' => $user?->name ?? null,
        'canal' => 'trip.' . $tripId,
    ]);
    return true; // O verifica si el usuario puede ver el viaje
});


Broadcast::channel('trip.{tripId}.status', function ($user = null, $tripId) {
    Log::info('[Broadcast] Intento de conexión al canal trip.' . $tripId . '.status', [
        'user_id' => $user?->id ?? null,
        'user' => $user?->name ?? null,
        'canal' => 'trip.' . $tripId . '.status',
    ]);
    return true;
});

Broadcast::channel('trip.{tripId}.chat', function ($user = null, $tripId) {
    Log::info('[Broadcast] Intento de conexión al canal trip.' . $tripId . '.chat', [
        'user_id' => $user?->id ?? null,
        'user' => $user?->name ?? null,
        'canal' => 'trip.' . $tripId . '.chat',
    ]);
    return true;
});

Broadcast::channel('trip.{tripId}.alerts', function ($user = null, $tripId) {
    Log::info('[Broadcast] Intento de conexión al canal trip.' . $tripId . '.alerts', [
        'user_id' => $user?->id ?? null,
        'user' => $user?->name ?? null,
        'canal' => 'trip.' . $tripId . '.alerts',
    ]);
    return true;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    Log::info('[Broadcast] Intento de conexión al canal user.' . $userId, [
        'user_id' => $user?->id,
        'user' => $user?->name ?? null,
        'canal' => 'user.' . $userId,
    ]);
    return (int) $user->id === (int) $userId;
});

// Canal de presencia para monitoreo de usuarios conectados a viajes
Broadcast::channel('trips.presence', function ($user) {
    Log::info('[Broadcast] Intento de conexión al canal trips.presence', [
        'user_id' => $user?->id,
        'user' => $user?->name ?? null,
        'canal' => 'trips.presence',
    ]);
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role ?? null,
    ];
});

// Canal de presencia por viaje para monitoreo de usuarios conectados a un viaje específico
Broadcast::channel('trip.{tripId}.presence', function ($user, $tripId) {
    Log::info('[Broadcast] Intento de conexión al canal trip.' . $tripId . '.presence', [
        'user_id' => $user?->id,
        'user' => $user?->name ?? null,
        'canal' => 'trip.' . $tripId . '.presence',
    ]);
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role ?? null,
    ];
});


// Canal global para sincronización de datos entre apps
Broadcast::channel('apps.sync', function ($user = null) {
    Log::info('[Broadcast] Intento de conexión al canal apps.sync', [
        'user_id' => $user?->id ?? null,
        'user' => $user?->name ?? null,
        'canal' => 'apps.sync',
    ]);
    return true;
});

// Canal de sincronización por viaje
Broadcast::channel('trip.{tripId}.sync', function ($user = null, $tripId) {
    Log::info('[Broadcast] Intento de conexión al canal trip.' . $tripId . '.sync', [
        'user_id' => $user?->id ?? null,
        'user' => $user?->name ?? null,
        'canal' => 'trip.' . $tripId . '.sync',
    ]);
    return true;
});