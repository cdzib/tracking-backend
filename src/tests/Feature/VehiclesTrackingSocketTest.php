<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Events\VehiclesTrackingUpdated;

class VehiclesTrackingSocketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_broadcasts_vehicles_tracking_updated_event()
    {
        Event::fake();

        $data = ['vehicle_id' => 1, 'lat' => 20.97, 'lng' => -89.62];
        event(new VehiclesTrackingUpdated($data));

        Event::assertDispatched(VehiclesTrackingUpdated::class, function ($event) use ($data) {
            return $event->data === $data;
        });
    }
}
