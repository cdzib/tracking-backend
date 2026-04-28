<?php

namespace App\Events;

use App\Models\Vehicle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpeedingDetected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle;
    public $speed;

    public function __construct(Vehicle $vehicle, $speed)
    {
        $this->vehicle = $vehicle;
        $this->speed = $speed;
    }

    public function broadcastOn()
    {
        return new Channel('tracking.alerts');
    }

    public function broadcastAs()
    {
        return 'alert.speeding';
    }

    public function broadcastWith()
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'plate' => $this->vehicle->license_plate,
            'speed' => $this->speed,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
