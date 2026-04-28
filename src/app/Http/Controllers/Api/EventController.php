<?php

namespace App\Http\Controllers\Api;

use App\Models\GpsEvent;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EventController
{
    /**
     * GET /api/tracking/events
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'event_type' => 'nullable|string',
            'severity' => 'nullable|in:info,warning,danger,critical',
            'status' => 'nullable|in:new,acknowledged,resolved',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $query = GpsEvent::query();

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->has('end_date')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $limit = $request->integer('limit', 100);

        $events = $query->latest('occurred_at')
            ->with('vehicle', 'gpsDevice')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $events->count(),
            'data' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'vehicle' => [
                        'id' => $event->vehicle->id,
                        'plate' => $event->vehicle->license_plate,
                    ],
                    'event' => [
                        'type' => $event->event_type,
                        'name' => $event->event_name,
                        'description' => $event->description,
                        'icon' => $event->getIcon(),
                    ],
                    'severity' => $event->severity,
                    'status' => $event->status,
                    'location' => $event->latitude && $event->longitude ? [
                        'latitude' => $event->latitude,
                        'longitude' => $event->longitude,
                    ] : null,
                    'data' => $event->event_data,
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                    'acknowledged_at' => $event->acknowledged_at?->toIso8601String(),
                    'notes' => $event->notes,
                ];
            })
        ]);
    }

    /**
     * POST /api/tracking/events/{eventId}/acknowledge
     */
    public function acknowledge($eventId, Request $request): JsonResponse
    {
        $event = GpsEvent::findOrFail($eventId);
        
        $event->acknowledge($request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'Event acknowledged',
            'data' => [
                'event_id' => $event->id,
                'status' => $event->status,
                'acknowledged_at' => $event->acknowledged_at,
            ]
        ]);
    }

    /**
     * GET /api/tracking/events/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:30',
        ]);

        $days = $request->integer('days', 7);
        $startDate = Carbon::now()->subDays($days);

        $events = GpsEvent::where('occurred_at', '>=', $startDate)
            ->get();

        $summary = [
            'total_events' => $events->count(),
            'by_severity' => $events->groupBy('severity')->map->count(),
            'by_type' => $events->groupBy('event_type')->map->count(),
            'by_status' => $events->groupBy('status')->map->count(),
            'unacknowledged' => $events->where('status', 'new')->count(),
            'critical_count' => $events->where('severity', 'critical')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}