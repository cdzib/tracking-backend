<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\TripChatMessage;

class TripChatController extends Controller
{
    /**
     * Enviar mensaje de chat a un viaje
     * POST /api/trips/{trip}/chat
     * Body: { "user_id": 1, "user_name": "Juan", "message": "Hola!" }
     */
    public function send(Request $request, $tripId)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'user_name' => 'required|string',
            'message' => 'required|string',
        ]);

        // Guardar mensaje en la base de datos
        \App\Models\TripChatMessage::create([
            'trip_id' => $tripId,
            'user_id' => $validated['user_id'],
            'user_name' => $validated['user_name'],
            'message' => $validated['message'],
            'created_at' => now(),
        ]);

        broadcast(new \App\Events\TripChatMessage(
            $tripId,
            $validated['user_id'],
            $validated['user_name'],
            $validated['message']
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Obtener mensajes de chat de un viaje
     * GET /api/trips/{trip}/chat
     */
    public function index($tripId)
    {
        $messages = \App\Models\TripChatMessage::where('trip_id', $tripId)
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($messages);
    }
}
