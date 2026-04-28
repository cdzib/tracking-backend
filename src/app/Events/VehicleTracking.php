<?php


namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Models\GpsDevice;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\VehicleTrackingResource;

class VehicleTracking implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vehicle;
    public $location;
    public $device;

    public function __construct(Vehicle $vehicle, VehicleLocation $location, GpsDevice $device)
    {
        $this->vehicle = $vehicle;
        $this->location = $location;
        $this->device = $device;

        Log::info('[VehicleTracking] Evento emitido', [
            'vehicle_id' => $vehicle->id,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'plate' => $vehicle->plate,
            'status' => $vehicle->status,
            'location' => $location,
            'device' => $device,
        ]);
    }

    public function broadcastOn()
    {
        return [
            new Channel('vehicles.tracking'),
            new Channel('vehicle.' . $this->vehicle->id),
        ];
    }

    public function broadcastAs()
    {
        return 'vehicle.tracking.updated';
    }

    public function broadcastWith()
    {
        $trip = $this->vehicle->trips()
            ->with(['bookings' => function ($query) {
                $query->where('status', 'active');
            }])
            ->latest('datetime')
            ->first();

        $seats = [];
        if ($trip) {
            foreach ($trip->bookings as $booking) {
                foreach (($booking->seats ?? []) as $seat) {
                    $seats[] = $seat;
                }
            }
        }

        return (new VehicleTrackingResource($this))->toArray(request());
    }
}
