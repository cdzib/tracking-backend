<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassiveBookingSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bookings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Crear 200 pasajeros
        $passengers = Passenger::factory(200)->create();

        // Obtener viajes existentes
        $trips = Trip::all();

        // Crear 400 bookings
        foreach (range(1, 400) as $i) {
            $trip = $trips->random();
            $passenger = $passengers->random();
            $seat = rand(1, $trip->vehicle->capacity ?? 12);
            Booking::create([
                'trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'status' => ['active', 'used', 'cancelled'][rand(0, 2)],
                'seats' => [
                    ['seat' => $seat, 'qr' => Str::uuid()->toString()]
                ],
            ]);
        }
    }
}
