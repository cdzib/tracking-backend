<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

$canAccessTrip = function ($user, $tripId): bool {
    if (! $user) {
        return false;
    }

    if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
        return true;
    }

    return Booking::where('trip_id', $tripId)
        ->where('passenger_id', $user->id)
        ->where('status', 'active')
        ->exists();
};

Broadcast::channel('trip.{tripId}', function ($user, $tripId) use ($canAccessTrip) {
    Log::info('[Broadcast] trip channel auth', [
        'user_id' => $user?->id,
        'channel' => 'trip.' . $tripId,
    ]);

    return $canAccessTrip($user, $tripId);
});

Broadcast::channel('trip.{tripId}.status', function ($user, $tripId) {
    Log::info('[Broadcast] trip status channel auth', [
        'user_id' => $user?->id ?? null,
        'channel' => 'trip.' . $tripId . '.status',
    ]);

    return true;
});

Broadcast::channel('trip.{tripId}.chat', function ($user, $tripId) {
    Log::info('[Broadcast] trip chat channel auth', [
        'user_id' => $user?->id ?? null,
        'channel' => 'trip.' . $tripId . '.chat',
    ]);

    return true;
});

Broadcast::channel('trip.{tripId}.alerts', function ($user, $tripId) {
    Log::info('[Broadcast] trip alerts channel auth', [
        'user_id' => $user?->id ?? null,
        'channel' => 'trip.' . $tripId . '.alerts',
    ]);

    return true;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    Log::info('[Broadcast] user channel auth', [
        'user_id' => $user?->id,
        'channel' => 'user.' . $userId,
    ]);

    return (int) $user->id === (int) $userId;
});

Broadcast::channel('trips.presence', function ($user) {
    Log::info('[Broadcast] trips presence channel auth', [
        'user_id' => $user?->id,
        'channel' => 'trips.presence',
    ]);

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role ?? null,
    ];
});

Broadcast::channel('trip.{tripId}.presence', function ($user, $tripId) use ($canAccessTrip) {
    Log::info('[Broadcast] trip presence channel auth', [
        'user_id' => $user?->id,
        'channel' => 'trip.' . $tripId . '.presence',
    ]);

    if (! $canAccessTrip($user, $tripId)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role ?? null,
    ];
});

Broadcast::channel('apps.sync', function ($user = null) {
    Log::info('[Broadcast] apps sync channel auth', [
        'user_id' => $user?->id ?? null,
        'channel' => 'apps.sync',
    ]);

    return true;
});

Broadcast::channel('trip.{tripId}.sync', function ($user, $tripId) {
    Log::info('[Broadcast] trip sync channel auth', [
        'user_id' => $user?->id ?? null,
        'channel' => 'trip.' . $tripId . '.sync',
    ]);

    return true;
});
