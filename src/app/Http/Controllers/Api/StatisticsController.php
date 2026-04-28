<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Models\GpsEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StatisticsController
{
    /**
     * GET /api/tracking/statistics/distance
     */
    public function distance(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        $locations = VehicleLocation::where('vehicle_id', $request->vehicle_id)
            ->whereBetween('recorded_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->orderBy('recorded_at', 'asc')
            ->get();

        $distance = $this->calculateDistance($locations);

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_id' => $request->vehicle_id,
                'distance_km' => round($distance / 1000, 2),
                'distance_miles' => round($distance / 1609.34, 2),
                'period' => [
                    'from' => $request->start_date,
                    'to' => $request->end_date,
                ]
            ]
        ]);
    }

    /**
     * GET /api/tracking/statistics/speed
     */
    public function speed(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $locations = VehicleLocation::where('vehicle_id', $request->vehicle_id)
            ->whereBetween('recorded_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->get();

        $speeds = $locations->pluck('speed')->filter(fn($s) => $s > 0);

        return response()->json([
            'success' => true,
            'data' => [
                'max_speed' => $speeds->max() ?? 0,
                'min_speed' => $speeds->min() ?? 0,
                'avg_speed' => round($speeds->avg() ?? 0, 2),
                'median_speed' => round($this->calculateMedian($speeds), 2),
                'readings_count' => $speeds->count(),
            ]
        ]);
    }

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

        return $distance;
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    private function calculateMedian($values)
    {
        $sorted = $values->sort()->values();
        $count = $sorted->count();
        
        if ($count === 0) return 0;
        if ($count % 2 === 1) return $sorted[floor($count / 2)];
        
        return ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2;
    }
}