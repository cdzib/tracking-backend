<?php

namespace App\Events;

use App\Models\GpsEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

class EventAcknowledged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GpsEvent $event;

    /**
     * Create a new event instance.
     */
    public function __construct(GpsEvent $event)
    {
        $this->event = $event;
    }

    public function broadcastOn(): Channel
    {
        // Puedes personalizar el canal según tu lógica de negocio
        return new Channel('gps-events');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->event->id,
            'vehicle_id' => $this->event->vehicle_id,
            'gps_device_id' => $this->event->gps_device_id,
            'event_type' => $this->event->event_type,
            'event_name' => $this->event->event_name,
            'description' => $this->event->description,
            'severity' => $this->event->severity,
            'status' => $this->event->status,
            'acknowledged_at' => optional($this->event->acknowledged_at)?->toIso8601String(),
            'notes' => $this->event->notes,
            'occurred_at' => optional($this->event->occurred_at)?->toIso8601String(),
        ];
    }
}
