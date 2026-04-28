<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'plate',
        'capacity',
        'status',
        'driver_id',
        'lat',
        'lng',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
    public function gpsDevice()
    {
        return $this->hasOne(GpsDevice::class);
    }

    public function locations()
    {
        return $this->hasMany(VehicleLocation::class);
    }

    public function currentLocation()
    {
        return $this->locations()->latest('recorded_at')->first();
    }

    public function currentTrip()
    {
        return $this->hasOne(Trip::class)->where('status', 'on_route');
    }

}
