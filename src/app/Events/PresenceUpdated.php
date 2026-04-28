<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $users;
    public $type;
    public $timestamp;

    /**
     * @param string $channel Nombre del canal de presencia
     * @param array $users Lista de usuarios conectados
     * @param string $type join|leave|sync
     */
    public function __construct($channel, $users, $type = 'sync')
    {
        $this->channel = $channel;
        $this->users = $users;
        $this->type = $type;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return [
            new PresenceChannel($this->channel),
        ];
    }

    public function broadcastWith()
    {
        return [
            'users' => $this->users,
            'type' => $this->type,
            'timestamp' => $this->timestamp,
        ];
    }
}
