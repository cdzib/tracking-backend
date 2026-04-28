<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $seat;
    public $status; // 'occupied' | 'available'
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($tripId, $seat, $status, $userId = null)
    {
        $this->tripId = $tripId;
        $this->seat = $seat;
        $this->status = $status;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('trip.' . $this->tripId . '.seats');
    }

    public function broadcastWith()
    {
        return [
            'trip_id' => $this->tripId,
            'seat' => $this->seat,
            'status' => $this->status,
            'user_id' => $this->userId,
        ];
    }

    public function broadcastAs()
    {
        return 'SeatStatusChanged';
    }
}
