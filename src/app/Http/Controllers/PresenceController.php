<?php

namespace App\Http\Controllers;

use App\Events\PresenceUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PresenceController extends Controller
{
    /**
     * Sincronizar usuarios conectados en el canal de presencia.
     * (Llamar desde el frontend cuando se conecte/desconecte)
     */
    public function sync(Request $request)
    {
        $channel = $request->input('channel', 'trips.presence');
        $users = $request->input('users', []);
        $type = $request->input('type', 'sync');

        // Puedes guardar la lista en cache si lo deseas
        Cache::put('presence:' . $channel, $users, 60);

        broadcast(new PresenceUpdated($channel, $users, $type))->toOthers();

        return response()->json(['status' => 'ok', 'channel' => $channel, 'users' => $users]);
    }
}
