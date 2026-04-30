<?php

namespace App\Events;

use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle;
    public $location;


    public function __construct(Vehicle $vehicle, VehicleLocation $location)
    {
        $this->vehicle = $vehicle;
        $this->location = $location;
        Log::info('[LocationUpdated] Evento emitido', [
            'vehicle_id' => $vehicle->id,
            'plate' => $vehicle->plate,
            'lat' => $location->latitude,
            'lng' => $location->longitude,
            'payload' => $this->broadcastWith(),
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('tracking.vehicles');
    }

    public function broadcastAs()
    {
        return 'location.updated';
    }

    public function broadcastWith()
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'plate' => $this->vehicle->plate,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'speed' => $this->location->speed,
            'course' => $this->location->course,
            'altitude' => $this->location->altitude,
            'battery' => $this->vehicle->gpsDevice->battery_level,
            'updated_at' => $this->location->recorded_at->toIso8601String(),
        ];
    }
}
