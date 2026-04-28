<?php

namespace App\Http\Controllers\Api;

use App\Models\GpsGeofence;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeofenceController
{
    /**
     * GET /api/tracking/geofences
     */
    public function index(Request $request): JsonResponse
    {
        $geofences = GpsGeofence::where('user_id', auth()->id())
            ->with('vehicle')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $geofences->count(),
            'data' => $geofences->map(fn($gf) => $this->formatGeofence($gf))
        ]);
    }

    /**
     * POST /api/tracking/geofences
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'shape_type' => 'required|in:circle,polygon',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'center_latitude' => 'required_if:shape_type,circle|numeric|between:-90,90',
            'center_longitude' => 'required_if:shape_type,circle|numeric|between:-180,180',
            'radius' => 'required_if:shape_type,circle|integer|min:10',
            'polygon_points' => 'required_if:shape_type,polygon|array',
            'notify_on_enter' => 'boolean',
            'notify_on_exit' => 'boolean',
            'alert_method' => 'in:email,sms,push,all',
        ]);

        $geofence = GpsGeofence::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Geofence created',
            'data' => $this->formatGeofence($geofence)
        ], 201);
    }

    /**
     * PUT /api/tracking/geofences/{id}
     */
    public function update($id, Request $request): JsonResponse
    {
        $geofence = GpsGeofence::findOrFail($id);
        
        // Verificar permisos
        if ($geofence->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'notify_on_enter' => 'boolean',
            'notify_on_exit' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $geofence->update($validated);

        return response()->json([
            'success' => true,
            'data' => $this->formatGeofence($geofence)
        ]);
    }

    /**
     * DELETE /api/tracking/geofences/{id}
     */
    public function destroy($id): JsonResponse
    {
        $geofence = GpsGeofence::findOrFail($id);

        if ($geofence->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $geofence->delete();

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/tracking/geofences/{id}/test
     * Probar si una ubicación está dentro
     */
    public function testLocation($id, Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $geofence = GpsGeofence::findOrFail($id);
        $isInside = $geofence->contains(
            $request->latitude,
            $request->longitude
        );

        return response()->json([
            'success' => true,
            'data' => [
                'geofence_id' => $geofence->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_inside' => $isInside,
            ]
        ]);
    }

    private function formatGeofence($geofence)
    {
        return [
            'id' => $geofence->id,
            'name' => $geofence->name,
            'description' => $geofence->description,
            'shape_type' => $geofence->shape_type,
            'vehicle_id' => $geofence->vehicle_id,
            'vehicle_plate' => $geofence->vehicle?->license_plate,
            'center' => $geofence->shape_type === 'circle' ? [
                'latitude' => $geofence->center_latitude,
                'longitude' => $geofence->center_longitude,
                'radius' => $geofence->radius,
            ] : null,
            'polygon_points' => $geofence->polygon_points,
            'notifications' => [
                'on_enter' => $geofence->notify_on_enter,
                'on_exit' => $geofence->notify_on_exit,
                'method' => $geofence->alert_method,
            ],
            'is_active' => $geofence->is_active,
            'created_at' => $geofence->created_at,
        ];
    }
}