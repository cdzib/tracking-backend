<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Support\Facades\DB;

class MassiveRouteStopSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('stops')->truncate();
        DB::table('routes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Crear 10 rutas
        $routes = collect();
        foreach (range(1, 10) as $i) {
            $routes->push(Route::create([
                'name' => 'Ruta ' . $i,
            ]));
        }

        // Crear 8 paradas por ruta
        foreach ($routes as $route) {
            foreach (range(1, 8) as $j) {
                Stop::create([
                    'route_id' => $route->id,
                    'name' => 'Parada ' . $j . ' de ' . $route->name,
                    'lat' => 20.98 + rand(-1000, 1000) / 10000,
                    'lng' => -89.62 + rand(-1000, 1000) / 10000,
                    'order' => $j,
                ]);
            }
        }
    }
}
