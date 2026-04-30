<?php

namespace App\Events;

use App\Models\Vehicle;
use App\Models\GpsGeofence;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeofenceEntered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle;
    public $geofence;

    public function __construct(Vehicle $vehicle, GpsGeofence $geofence)
    {
        $this->vehicle = $vehicle;
        $this->geofence = $geofence;
    }

    public function broadcastOn()
    {
        return new Channel('tracking.alerts');
    }

    public function broadcastAs()
    {
        return 'alert.geofence_entered';
    }

    public function broadcastWith()
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'plate' => $this->vehicle->plate,
            'geofence_name' => $this->geofence->name,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
