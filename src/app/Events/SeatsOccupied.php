<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatsOccupied implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $seats;

    public function __construct($tripId, $seats)
    {
        $this->tripId = $tripId;
        $this->seats = $seats;
    }

    public function broadcastOn()
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('trip.' . $this->tripId),
            new \Illuminate\Broadcasting\Channel('trips.tracking'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'trip_id' => $this->tripId,
            'seats'   => $this->seats,
        ];
    }
}
