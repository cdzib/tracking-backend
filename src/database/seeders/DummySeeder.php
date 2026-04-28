<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Vehicle;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Stop;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'manage vans', 'manage trips', 'manage bookings', 'manage routes', 'manage drivers', 'manage passengers', 'manage stops', 'manage roles', 'manage permissions'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Crear roles y asignar permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);
        $driverRole = Role::firstOrCreate(['name' => 'driver']);
        $driverRole->syncPermissions(['manage trips']);
        $passengerRole = Role::firstOrCreate(['name' => 'passenger']);

        // Crear usuario admin
        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Crear un driver
        $driver = Driver::firstOrCreate([
            'email' => 'driver@demo.com',
        ], [
            'name' => 'Demo Driver',
            'password' => bcrypt('password'),
            'phone' => '999999999',
        ]);

        // Crear un passenger
        $passenger = Passenger::firstOrCreate([
            'email' => 'passenger@demo.com',
        ], [
            'name' => 'Demo Passenger',
            'password' => bcrypt('password'),
            'phone' => '888888888',
        ]);

        // Crear una vehicle
        $vehicle = Vehicle::create([
            'plate' => 'ABC123',
            'capacity' => 12,
            'status' => 'available',
            'driver_id' => $driver->id,
            'lat' => 20.9800,
            'lng' => -89.6200,
        ]);


        // Crear una ruta
        $route = Route::create([
            'name' => 'Ruta Centro',
        ]);

        // Crear paradas normalizadas
        $stopsData = [
            ['name' => 'Parada 1', 'lat' => 20.9801, 'lng' => -89.6201, 'order' => 1],
            ['name' => 'Parada 2', 'lat' => 20.9802, 'lng' => -89.6202, 'order' => 2],
        ];
        foreach ($stopsData as $stopData) {
            Stop::create([
                'route_id' => $route->id,
                'name' => $stopData['name'],
                'lat' => $stopData['lat'],
                'lng' => $stopData['lng'],
                'order' => $stopData['order'],
            ]);
        }

        // Crear horarios normalizados
        $schedulesData = ['08:00', '12:00', '18:00'];
        foreach ($schedulesData as $time) {
            \App\Models\Schedule::create([
                'route_id' => $route->id,
                'departure_time' => $time,
            ]);
        }

        // Crear un trip
        $trip = Trip::create([
            'vehicle_id' => $vehicle->id,
            'route_id' => $route->id,
            'status' => 'pending',
            'datetime' => now()->addHour(),
        ]);

        // Crear una reserva
        Booking::create([
            'trip_id' => $trip->id,
            'passenger_id' => $passenger->id,
            'status' => 'active',
            'seats' => [
                ['seat' => 1, 'qr' => (string) \Illuminate\Support\Str::uuid()],
            ],
        ]);
    }
}
