<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Trip;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TripStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trip;

    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
    }

    public function broadcastOn()
    {
        return [
            new Channel('trips.status'),
            new Channel('trip.' . $this->trip->id . '.status'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->trip->id,
            'status' => $this->trip->status,
            'datetime' => $this->trip->datetime,
            'van_id' => $this->trip->van_id,
            'route_id' => $this->trip->route_id,
        ];
    }
}
