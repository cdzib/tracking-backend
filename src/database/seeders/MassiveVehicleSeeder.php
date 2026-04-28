<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class MassiveVehicleSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('trips')->truncate();
        DB::table('vehicles')->truncate();
        DB::table('drivers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Crear 50 conductores
        $drivers = Driver::factory(50)->create();

        // Crear 100 vehículos
        $vehicles = collect();
        // Primero, asignar un vehículo a cada chofer
        foreach ($drivers as $idx => $driver) {
            $vehicles->push(Vehicle::create([
                'plate' => 'ABC' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'capacity' => rand(8, 15),
                'status' => 'available',
                'driver_id' => $driver->id,
                'lat' => 20.98 + rand(-1000, 1000) / 10000,
                'lng' => -89.62 + rand(-1000, 1000) / 10000,
            ]));
        }
        // El resto de vehículos se asignan aleatoriamente
        for ($i = $drivers->count() + 1; $i <= 100; $i++) {
            $vehicles->push(Vehicle::create([
                'plate' => 'ABC' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'capacity' => rand(8, 15),
                'status' => 'available',
                'driver_id' => $drivers->random()->id,
                'lat' => 20.98 + rand(-1000, 1000) / 10000,
                'lng' => -89.62 + rand(-1000, 1000) / 10000,
            ]));
        }

        // Crear 200 viajes
        foreach (range(1, 200) as $i) {
            Trip::create([
                'vehicle_id' => $vehicles->random()->id,
                'route_id' => rand(1, 10), // Ajusta según tus rutas
                'status' => ['pending', 'assigned', 'picking_up', 'on_route', 'arrived', 'completed', 'cancelled'][rand(0,6)],
                'datetime' => now()->addMinutes(rand(-10000, 10000)),
            ]);
        }
    }
}
