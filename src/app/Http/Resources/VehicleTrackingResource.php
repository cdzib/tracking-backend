<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleTrackingResource extends JsonResource
{
    public function toArray($request)
    {
        $vehicle = $this->vehicle ?? $this; // Permite usar tanto modelo como array
        $device = $vehicle->gpsDevice ?? $this->device ?? null;
        $location = $device && method_exists($device, 'currentLocation') ? $device->currentLocation() : ($this->location ?? null);
        $trip = $vehicle->currentTrip ?? null;
        return [
            'vehicle' => [
                'id' => $vehicle->id,
                'plate' => $vehicle->plate ?? $vehicle->license_plate ?? null,
                'status' => $vehicle->status,
            ],
            'device' => $device ? [
                'id' => $device->id,
                'imei' => $device->imei,
                'status' => $device->status,
                'latitude' => (float)($device->latitude ?? 0),
                'longitude' => (float)($device->longitude ?? 0),
                'battery' => [
                    'level' => $device->battery_level,
                    'status' => $device->battery_status ?? $device->status,
                ],
                'signal_strength' => $device->signal_strength,
                'last_update' => $device->last_update,
            ] : null,
            'location' => $location ? [
                'latitude' => (float)$location->latitude,
                'longitude' => (float)$location->longitude,
                'altitude' => $location->altitude,
                'speed' => (float)$location->speed,
                'course' => $location->course,
                'direction' => $location->getCompassDirection(),
                'accuracy' => (float)$location->accuracy,
                'satellites' => $location->satellites,
                'recorded_at' => $location->recorded_at->toIso8601String(),
                'human_time' => $location->recorded_at->diffForHumans(),
            ] : null,
            // Ruta (historial de ubicaciones)
        
            // Viaje actual
            'trip' => $trip,
            // Reservación actual
        ];
    }
}
