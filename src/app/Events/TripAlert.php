<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripAlert implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $type;
    public $message;
    public $data;
    public $timestamp;

    public function __construct($tripId, $type, $message, $data = [])
    {
        $this->tripId = $tripId;
        $this->type = $type; // Ej: 'parada', 'incidente', 'info'
        $this->message = $message;
        $this->data = $data;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return [
            new Channel('trip.' . $this->tripId . '.alerts'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'trip_id' => $this->tripId,
            'type' => $this->type,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ];
    }
}
