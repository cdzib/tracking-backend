<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $title;
    public $message;
    public $data;
    public $timestamp;

    public function __construct($userId, $title, $message, $data = [])
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastWith()
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ];
    }
}
