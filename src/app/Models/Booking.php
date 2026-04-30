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

    public static function hasOccupiedSeats(int $tripId, array $seatNumbers, ?int $exceptBookingId = null): bool
    {
        $query = static::where('trip_id', $tripId)
            ->where('status', 'active')
            ->where(function ($query) use ($seatNumbers) {
                foreach ($seatNumbers as $seat) {
                    $query->orWhereJsonContains('seats', ['seat' => (int) $seat]);
                }
        });

        if ($exceptBookingId) {
            $query->where('id', '!=', $exceptBookingId);
        }

        return $query->exists();
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
