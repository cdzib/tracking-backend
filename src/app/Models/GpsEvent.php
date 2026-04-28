<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsEvent extends Model
{
    protected $fillable = [
        'vehicle_id', 'gps_device_id', 'event_type', 'event_name',
        'description', 'event_data', 'latitude', 'longitude',
        'severity', 'status', 'acknowledged_at', 'notes', 'occurred_at'
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'event_data' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function gpsDevice(): BelongsTo
    {
        return $this->belongsTo(GpsDevice::class);
    }

    /**
     * Marcar evento como reconocido
     */
    public function acknowledge(string $notes = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'notes' => $notes
        ]);
        event(new \App\Events\EventAcknowledged($this));
    }

    /**
     * Obtener icono por tipo de evento
     */
    public function getIcon(): string
    {
        return match($this->event_type) {
            'engine_start' => '🚗',
            'engine_stop' => '⛔',
            'speeding' => '⚡',
            'harsh_acceleration' => '🔝',
            'harsh_braking' => '🛑',
            'geofence_enter' => '📍',
            'geofence_exit' => '❌',
            'offline' => '📡',
            'collision' => '💥',
            default => '📌'
        };
    }
}
