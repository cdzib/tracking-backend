<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripChatMessage extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'trip_id',
        'user_id',
        'user_name',
        'message',
        'created_at',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
