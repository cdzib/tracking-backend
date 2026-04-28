<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataSync implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $payload;
    public $type;
    public $timestamp;

    /**
     * @param string $channel Canal de sincronización (apps.sync o trip.{tripId}.sync)
     * @param array $payload Datos a sincronizar
     * @param string $type Tipo de sincronización (ej: update, delete, create, custom)
     */
    public function __construct($channel, $payload, $type = 'update')
    {
        $this->channel = $channel;
        $this->payload = $payload;
        $this->type = $type;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return [
            new Channel($this->channel),
        ];
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'payload' => $this->payload,
            'timestamp' => $this->timestamp,
        ];
    }
}
