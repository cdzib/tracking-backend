<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class GpsDevice extends Model
{
    protected $fillable = [
        'vehicle_id',
        'imei',
        'device_name',
        'device_model',
        'device_brand',
        'latitude',
        'longitude',
        'altitude',
        'speed',
        'course',
        'accuracy',
        'last_update',
        'status',
        'battery_level',
        'signal_strength',
        'phone_number',
        'sim_operator',
        'voltage',
        'gps_update_interval',
        'report_interval',
        'config',
        'last_online',
        'first_seen',
        'last_command_sent',
        'last_command',
        'total_distance',
        'trips_count',
        'error_count'
    ];

    protected $casts = [
        'last_update' => 'datetime',
        'last_online' => 'datetime',
        'first_seen' => 'datetime',
        'last_command_sent' => 'datetime',
        'last_command' => 'array',
        'config' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VehicleLocation::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(GpsEvent::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(GpsCommand::class);
    }

    /**
     * Obtener ubicación actual
     */
    public function currentLocation()
    {
        return $this->locations()
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Marcar como online
     */
    public function markOnline()
    {
        $this->update(['last_online' => now(), 'status' => 'active']);
        event(new \App\Events\DeviceOnline($this));
    }

    /**
     * Marcar como offline
     */
    public function markOffline()
    {
        $this->update(['status' => 'offline']);
        event(new \App\Events\DeviceOffline($this));
    }

    /**
     * Enviar comando al dispositivo
     */
    public function sendCommand(string $type, array $parameters = []): GpsCommand
    {
        return GpsCommand::create([
            'gps_device_id' => $this->id,
            'command_type' => $type,
            'parameters' => $parameters,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    /**
     * Obtener distancia total recorrida
     */
    public function getTotalDistance()
    {
        return $this->total_distance;
    }

    /**
     * Actualizar distancia basada en ubicaciones
     */
    public function calculateDistance()
    {
        $locations = $this->locations()->orderBy('recorded_at')->get();
        $distance = 0;

        for ($i = 0; $i < count($locations) - 1; $i++) {
            $distance += $this->haversineDistance(
                $locations[$i]->latitude,
                $locations[$i]->longitude,
                $locations[$i + 1]->latitude,
                $locations[$i + 1]->longitude
            );
        }

        $this->update(['total_distance' => (int)$distance]);
        return $distance;
    }

    /**
     * Fórmula Haversine para distancia entre coordenadas
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }

    /**
     * Obtener estado de batería con alerta
     */
    public function getBatteryStatus(): array
    {
        $level = $this->battery_level;

        return [
            'level' => $level,
            'status' => match (true) {
                $level < 10 => 'critical',
                $level < 20 => 'low',
                $level < 50 => 'medium',
                default => 'good'
            },
            'icon' => $this->getBatteryIcon($level),
        ];
    }

    private function getBatteryIcon(int $level): string
    {
        return match (true) {
            $level >= 75 => '🔋',
            $level >= 50 => '🪫',
            $level >= 25 => '⚠️',
            default => '⛔'
        };
    }
}
