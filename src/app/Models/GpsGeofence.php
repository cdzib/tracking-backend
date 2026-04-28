<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsGeofence extends Model
{
    protected $fillable = [
        'user_id', 'vehicle_id', 'name', 'description',
        'center_latitude', 'center_longitude', 'radius',
        'polygon_points', 'shape_type', 'notify_on_enter',
        'notify_on_exit', 'alert_method', 'is_active'
    ];

    protected $casts = [
        'polygon_points' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Verificar si una ubicación está dentro de la cerca
     */
    public function contains(float $latitude, float $longitude): bool
    {
        return match($this->shape_type) {
            'circle' => $this->isInCircle($latitude, $longitude),
            'polygon' => $this->isInPolygon($latitude, $longitude),
            default => false
        };
    }

    private function isInCircle(float $lat, float $lon): bool
    {
        $distance = $this->haversineDistance(
            $this->center_latitude,
            $this->center_longitude,
            $lat,
            $lon
        );

        return $distance <= $this->radius;
    }

    private function isInPolygon(float $lat, float $lon): bool
    {
        if (!$this->polygon_points) return false;

        $vertices = count($this->polygon_points);
        $inside = false;

        for ($i = 0, $j = $vertices - 1; $i < $vertices; $j = $i++) {
            $xi = $this->polygon_points[$i][1];
            $yi = $this->polygon_points[$i][0];
            $xj = $this->polygon_points[$j][1];
            $yj = $this->polygon_points[$j][0];

            $intersect = (($yi > $lon) != ($yj > $lon)) &&
                        ($lat < ($xj - $xi) * ($lon - $yi) / ($yj - $yi) + $xi);
            
            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }
}