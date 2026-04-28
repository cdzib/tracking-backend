<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'trip_id',
        'passenger_id',
        'status',
        'seats', // array de objetos {seat, qr}
    ];

    protected $casts = [
        'seats' => 'array',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
