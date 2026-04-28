<?php

namespace App\Http\Controllers\Api;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteController
{
    
    // GET /api/routes
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $routes = \App\Models\Route::paginate($perPage);
        return response()->json($routes);
    }
    // GET /api/routes/{id}/stops
    public function stops(Request $request, $id)
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');
        $route = Route::findOrFail($id);
        $stopsQuery = $route->stops();
        if ($search) {
            $stopsQuery->where('name', 'like', "%$search%");
        }
        $stops = $stopsQuery->paginate($perPage);
        return response()->json([
            'route_id' => $route->id,
            'stops' => $stops,
        ]);
    }

    // GET /api/routes/{id}/schedules
    public function schedules(Request $request, $id)
    {
        $perPage = $request->query('per_page', 15);
        $route = Route::findOrFail($id);
        $schedules = $route->schedules()->paginate($perPage);
        return response()->json([
            'route_id' => $route->id,
            'schedules' => $schedules,
        ]);
    }
}
