<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Models\GpsDevice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\VehicleTrackingResource;

class TrackingController
{
    /**
     * GET /api/tracking/vehicles/{vehicleId}/current-location
     * Obtener ubicación actual
     */
    public function currentLocation($vehicleId): JsonResponse
    {
        $vehicle = Vehicle::with('gpsDevice')->findOrFail($vehicleId);
        Log::info("Fetching current location for vehicle ID: {$vehicleId}");
        Log::debug("Vehicle details: " . json_encode($vehicle));
        if (!$vehicle->gpsDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle has no GPS device assigned'
            ], 404);
        }

        $vehicle = Vehicle::with('gpsDevice')->findOrFail($vehicleId);
        return response()->json([
            'success' => true,
            'data' => new VehicleTrackingResource($vehicle)
        ]);
    }

    /**
     * GET /api/tracking/vehicles/{vehicleId}/route-history
     * Obtener histórico de ruta
     */
    public function routeHistory($vehicleId, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:10000',
        ]);

        $vehicle = Vehicle::findOrFail($vehicleId);
        $limit = $request->input('limit', 1000);

        $locations = VehicleLocation::where('vehicle_id', $vehicleId)
            ->whereBetween('recorded_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->orderBy('recorded_at', 'asc')
            ->limit($limit)
            ->get();

        // Calcular estadísticas
        $stats = [
            'total_points' => $locations->count(),
            'distance' => $this->calculateDistance($locations),
            'max_speed' => $locations->max('speed') ?? 0,
            'avg_speed' => $locations->avg('speed') ?? 0,
            'time_range' => $locations->count() > 0 ? [
                'start' => $locations->first()->recorded_at,
                'end' => $locations->last()->recorded_at,
                'duration_seconds' => $locations->first()->recorded_at->diffInSeconds($locations->last()->recorded_at),
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_id' => $vehicleId,
                'date_range' => [
                    'from' => $request->start_date,
                    'to' => $request->end_date,
                ],
                'statistics' => $stats,
                'locations' => $locations->map(fn($loc) => [
                    'latitude' => (float)$loc->latitude,
                    'longitude' => (float)$loc->longitude,
                    'altitude' => $loc->altitude,
                    'speed' => (float)$loc->speed,
                    'course' => $loc->course,
                    'recorded_at' => $loc->recorded_at->toIso8601String(),
                ]),
            ]
        ]);
    }

    /**
     * GET /api/tracking/vehicles/all-locations
     * Obtener ubicación actual de todos los vehículos
     */
    public function allVehiclesLocations(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:active,idle,offline,maintenance,lost',
            'with_offline' => 'nullable|boolean',
        ]);

        $query = Vehicle::with(['gpsDevice', 'driver', 'currentTrip', 'currentTrip.route', 'currentTrip.bookings']);

        // Filtrar por estado del dispositivo
        if ($request->has('status')) {
            $query->whereHas(
                'gpsDevice',
                fn($q) =>
                $q->where('status', $request->status)
            );
        }

        // Excluir offline por defecto
        if (!$request->boolean('with_offline', false)) {
            $query->whereHas(
                'gpsDevice',
                fn($q) =>
                $q->whereIn('status', ['active', 'idle'])
            );
        }

        $vehicles = $query->get();
        log::info("Fetched all vehicles with locations", $vehicles->toArray());
        return response()->json([
            'success' => true,
            'count' => $vehicles->count(),
            'data' => VehicleTrackingResource::collection($vehicles)
        ]);
    }

    /**
     * GET /api/tracking/vehicles/{vehicleId}/trips
     * Obtener viajes (inicio-fin de movimiento)
     */
    public function getTrips($vehicleId, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $vehicle = Vehicle::findOrFail($vehicleId);
        $minSpeed = 2; // km/h para considerar movimiento

        $locations = VehicleLocation::where('vehicle_id', $vehicleId)
            ->whereBetween('recorded_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->orderBy('recorded_at', 'asc')
            ->get();

        $trips = [];
        $trip = null;

        foreach ($locations as $location) {
            $isMoving = $location->speed >= $minSpeed;

            if ($isMoving && !$trip) {
                // Iniciar nuevo viaje
                $trip = [
                    'start_location' => [
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                    'start_time' => $location->recorded_at,
                    'locations' => [$location],
                ];
            } elseif ($isMoving && $trip) {
                // Continuar viaje
                $trip['locations'][] = $location;
            } elseif (!$isMoving && $trip) {
                // Terminar viaje
                $trip['end_location'] = [
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                ];
                $trip['end_time'] = $location->recorded_at;

                // Calcular estadísticas
                $this->calculateTripStats($trip);
                $trips[] = $trip;
                $trip = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_id' => $vehicleId,
                'trips_count' => count($trips),
                'trips' => $trips
            ]
        ]);
    }

    /**
     * GET /api/tracking/vehicles/{vehicleId}/stops
     * Obtener paradas (tiempos sin movimiento)
     */
    public function getStops($vehicleId, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'min_duration' => 'nullable|integer|min:60', // segundos
        ]);

        $minDuration = $request->integer('min_duration', 300);
        $vehicle = Vehicle::findOrFail($vehicleId);

        $locations = VehicleLocation::where('vehicle_id', $vehicleId)
            ->whereBetween('recorded_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->orderBy('recorded_at', 'asc')
            ->get();

        $stops = [];
        $stop = null;

        foreach ($locations as $location) {
            $isMoving = $location->speed >= 2;

            if (!$isMoving && !$stop) {
                // Iniciar parada
                $stop = [
                    'location' => [
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                    'start_time' => $location->recorded_at,
                    'points' => [$location],
                ];
            } elseif (!$isMoving && $stop) {
                // Continuar parada
                $stop['points'][] = $location;
            } elseif ($isMoving && $stop) {
                // Terminar parada
                $stop['end_time'] = $locations->where('recorded_at', '<', $location->recorded_at)
                    ->last()?->recorded_at ?? $location->recorded_at->subSecond();
                $stop['duration'] = $stop['end_time']->diffInSeconds($stop['start_time']);

                if ($stop['duration'] >= $minDuration) {
                    $stops[] = $stop;
                }
                $stop = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_id' => $vehicleId,
                'stops_count' => count($stops),
                'total_stop_time' => array_sum(array_column($stops, 'duration')),
                'stops' => $stops
            ]
        ]);
    }

    /**
     * GET /api/tracking/devices/{deviceId}/health
     * Obtener estado del dispositivo
     */
    public function deviceHealth($deviceId): JsonResponse
    {
        $device = GpsDevice::findOrFail($deviceId);

        $lastLocation = $device->currentLocation();
        $lastUpdatedSeconds = $device->last_update ?
            $device->last_update->diffInSeconds(now()) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'device' => [
                    'id' => $device->id,
                    'imei' => $device->imei,
                    'status' => $device->status,
                ],
                'connectivity' => [
                    'status' => $lastUpdatedSeconds > 300 ? 'offline' : 'online',
                    'last_update' => $device->last_update,
                    'seconds_since_update' => $lastUpdatedSeconds,
                    'signal_strength' => $device->signal_strength,
                    'signal_bars' => ceil(($device->signal_strength ?? 0) / 31 * 5),
                ],
                'power' => [
                    'battery_level' => $device->battery_level,
                    'status' => $device->getBatteryStatus(),
                    'voltage' => $device->voltage,
                ],
                'location' => $lastLocation ? [
                    'latest' => [
                        'latitude' => $lastLocation->latitude,
                        'longitude' => $lastLocation->longitude,
                        'recorded_at' => $lastLocation->recorded_at,
                    ]
                ] : null,
                'pending_commands' => \App\Models\GpsCommand::where('gps_device_id', $device->id)
                    ->where('status', 'pending')
                    ->count(),
            ]
        ]);
    }

    /**
     * Calcular distancia entre múltiples puntos
     */
    private function calculateDistance($locations)
    {
        if ($locations->count() < 2) return 0;

        $distance = 0;
        $locs = $locations->toArray();

        for ($i = 0; $i < count($locs) - 1; $i++) {
            $distance += $this->haversineDistance(
                $locs[$i]['latitude'],
                $locs[$i]['longitude'],
                $locs[$i + 1]['latitude'],
                $locs[$i + 1]['longitude']
            );
        }

        return round($distance, 2);
    }

    /**
     * Fórmula Haversine
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Calcular estadísticas de viaje
     */
    private function calculateTripStats(&$trip)
    {
        $locations = collect($trip['locations']);

        $trip['statistics'] = [
            'duration' => $trip['start_time']->diffInSeconds($trip['end_time']),
            'distance' => $this->calculateDistance($locations),
            'max_speed' => $locations->max('speed'),
            'avg_speed' => $locations->avg('speed'),
            'points_count' => count($trip['locations']),
        ];
    }
}
