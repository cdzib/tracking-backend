<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'name',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function stops()
    {
        return $this->hasMany(Stop::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
