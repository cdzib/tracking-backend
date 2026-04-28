<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $userId;
    public $userName;
    public $message;
    public $timestamp;

    public function __construct($tripId, $userId, $userName, $message)
    {
        $this->tripId = $tripId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->message = $message;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return [
            new Channel('trip.' . $this->tripId . '.chat'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'trip_id' => $this->tripId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'message' => $this->message,
            'timestamp' => $this->timestamp,
        ];
    }
}
