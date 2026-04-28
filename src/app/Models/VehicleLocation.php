<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocation extends Model
{
    protected $fillable = [
        'vehicle_id', 
        'gps_device_id', 
        'latitude', 
        'longitude',
        'altitude', 
        'speed', 
        'course', 
        'accuracy', 
        'hdop',
        'recorded_at', 
        'satellites', 
        'battery_level', 
        'signal_strength',
        'raw_data'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'raw_data' => 'array',
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
     * Calcular velocidad promedio en km/h
     */
    public function getAverageSpeed($minutes = 5): float
    {
        $locations = VehicleLocation::where('gps_device_id', $this->gps_device_id)
            ->where('recorded_at', '>=', $this->recorded_at->subMinutes($minutes))
            ->orderBy('recorded_at')
            ->get();

        if ($locations->count() < 2) return 0;

        $speeds = $locations->pluck('speed')->filter(fn($s) => $s > 0);
        return $speeds->avg();
    }

    /**
     * Detectar cambios bruscos de velocidad
     */
    public function detectHarshBraking(float $threshold = 5.0): bool
    {
        $previous = VehicleLocation::where('gps_device_id', $this->gps_device_id)
            ->where('recorded_at', '<', $this->recorded_at)
            ->latest('recorded_at')
            ->first();

        if (!$previous) return false;

        $speedDifference = abs($previous->speed - $this->speed);
        return $speedDifference > $threshold;
    }

    /**
     * Obtener dirección legible (N, NE, E, etc)
     */
    public function getCompassDirection(): string
    {
        if (!$this->course) return 'N/A';
        
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                      'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = round($this->course / 22.5) % 16;
        return $directions[$index];
    }
}