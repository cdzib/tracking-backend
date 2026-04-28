<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\TripAlert;

class TripAlertController extends Controller
{
    /**
     * Enviar alerta a un viaje
     * POST /api/trips/{trip}/alert
     * Body: { "type": "parada", "message": "Llegando a la parada Centro", "data": {"stop_id": 5} }
     */
    public function send(Request $request, $tripId)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);

        broadcast(new TripAlert(
            $tripId,
            $validated['type'],
            $validated['message'],
            $validated['data'] ?? []
        ))->toOthers();

        return response()->json(['success' => true]);
    }
}
