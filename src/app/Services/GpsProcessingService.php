<?php

namespace App\Services;

use App\Models\GpsDevice;
use App\Models\VehicleLocation;
use App\Models\GpsEvent;
use App\Models\GpsGeofence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class GpsProcessingService
{
    /**
     * Procesar una actualización de ubicación
     */
    public function processLocation(GpsDevice $device, VehicleLocation $location)
    {
        $this->detectSpeeching($device, $location);
        $this->detectHarshBraking($device, $location);
        $this->checkGeofences($device, $location);
        $this->updateMovementStatus($device, $location);
    }

    /**
     * Detectar exceso de velocidad
     */
    private function detectSpeeching(GpsDevice $device, VehicleLocation $location)
    {
        $speedLimit = $device->config['speed_limit'] ?? 120; // km/h por defecto

        if ($location->speed > $speedLimit) {
            GpsEvent::create([
                'vehicle_id' => $device->vehicle_id,
                'gps_device_id' => $device->id,
                'event_type' => 'speeding',
                'event_name' => 'Exceso de velocidad',
                'description' => "Velocidad: {$location->speed} km/h (límite: {$speedLimit})",
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'severity' => $location->speed > $speedLimit * 1.2 ? 'danger' : 'warning',
                'event_data' => [
                    'current_speed' => $location->speed,
                    'speed_limit' => $speedLimit,
                    'exceeds_by' => $location->speed - $speedLimit,
                ],
                'occurred_at' => $location->recorded_at,
            ]);

            event(new \App\Events\SpeedingDetected($device->vehicle, $location->speed));
        }
    }

    /**
     * Detectar frenadas bruscas
     */
    private function detectHarshBraking(GpsDevice $device, VehicleLocation $location)
    {
        if ($location->detectHarshBraking(5.0)) { // 5 km/h de diferencia
            GpsEvent::create([
                'vehicle_id' => $device->vehicle_id,
                'gps_device_id' => $device->id,
                'event_type' => 'harsh_braking',
                'event_name' => 'Frenada brusca',
                'severity' => 'warning',
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'occurred_at' => $location->recorded_at,
            ]);

            event(new \App\Events\HarshBrakingDetected($device->vehicle, $location));
        }
    }

    /**
     * Verificar geofences
     */
    private function checkGeofences(GpsDevice $device, VehicleLocation $location)
    {
        $geofences = GpsGeofence::where('is_active', true)
            ->where(function ($q) use ($device) {
                $q->whereNull('vehicle_id')
                  ->orWhere('vehicle_id', $device->vehicle_id);
            })
            ->get();

        foreach ($geofences as $geofence) {
            $isInside = $geofence->contains($location->latitude, $location->longitude);
            
            // Obtener estado anterior desde cache
            $cacheKey = "geofence_{$geofence->id}_vehicle_{$device->vehicle_id}";
            $wasInside = Cache::get($cacheKey, false);

            // Detectar entrada
            if ($isInside && !$wasInside && $geofence->notify_on_enter) {
                GpsEvent::create([
                    'vehicle_id' => $device->vehicle_id,
                    'gps_device_id' => $device->id,
                    'event_type' => 'geofence_enter',
                    'event_name' => "Entrada a {$geofence->name}",
                    'severity' => 'info',
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'event_data' => ['geofence_id' => $geofence->id],
                    'occurred_at' => $location->recorded_at,
                ]);

                event(new \App\Events\GeofenceEntered($device->vehicle, $geofence));
            }

            // Detectar salida
            if (!$isInside && $wasInside && $geofence->notify_on_exit) {
                GpsEvent::create([
                    'vehicle_id' => $device->vehicle_id,
                    'gps_device_id' => $device->id,
                    'event_type' => 'geofence_exit',
                    'event_name' => "Salida de {$geofence->name}",
                    'severity' => 'warning',
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'event_data' => ['geofence_id' => $geofence->id],
                    'occurred_at' => $location->recorded_at,
                ]);

                event(new \App\Events\GeofenceExited($device->vehicle, $geofence));
            }

            // Actualizar cache
            Cache::put($cacheKey, $isInside, now()->addHours(1));
        }
    }

    /**
     * Actualizar estado de movimiento
     */
    private function updateMovementStatus(GpsDevice $device, VehicleLocation $location)
    {
        $threshold = 2; // km/h
        
        if ($location->speed < $threshold) {
            // Parado
            Cache::put("vehicle_{$device->vehicle_id}_moving", false, now()->addHours(1));
        } else {
            // En movimiento
            Cache::put("vehicle_{$device->vehicle_id}_moving", true, now()->addHours(1));
        }
    }
}