<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $table = 'trips';

    protected $fillable = [
        'vehicle_id',
        'route_id',
        'status',
        'datetime',
    ];

    protected $dates = [
        'datetime',
    ];

    protected static function booted()
    {
        static::updated(function ($trip) {
            if ($trip->isDirty('status')) {
                event(new \App\Events\TripStatusChanged($trip));
            }
        });
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
