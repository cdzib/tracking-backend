<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $action;

    public function __construct(Booking $booking, $action)
    {
        $this->booking = $booking;
        $this->action = $action; // created, updated, cancelled
    }

    public function broadcastOn()
    {
        return [
            new Channel('trip.' . $this->booking->trip_id . '.bookings'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->booking->id,
            'trip_id' => $this->booking->trip_id,
            'passenger_id' => $this->booking->passenger_id,
            'status' => $this->booking->status,
            'seats' => $this->booking->seats,
            'action' => $this->action,
        ];
    }
}
