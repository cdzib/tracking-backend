<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsCommand extends Model
{
    protected $fillable = [
        'gps_device_id', 'sent_by_user_id', 'command_type',
        'parameters', 'status', 'response', 'sent_at',
        'acknowledged_at', 'expires_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'expires_at' => 'datetime',
        'parameters' => 'array',
    ];

    public function gpsDevice(): BelongsTo
    {
        return $this->belongsTo(GpsDevice::class);
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    /**
     * Marcar como enviado
     */
    public function markSent()
    {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
    }

    /**
     * Marcar como confirmado
     */
    public function markAcknowledged(string $response = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'response' => $response
        ]);
    }

    /**
     * Verificar si expiró
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }
}
