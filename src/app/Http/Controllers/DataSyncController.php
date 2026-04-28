<?php

namespace App\Http\Controllers;

use App\Events\DataSync;
use Illuminate\Http\Request;

class DataSyncController extends Controller
{
    /**
     * Sincronizar datos globalmente entre apps.
     */
    public function syncGlobal(Request $request)
    {
        $payload = $request->input('payload', []);
        $type = $request->input('type', 'update');
        broadcast(new DataSync('apps.sync', $payload, $type))->toOthers();
        return response()->json(['status' => 'ok', 'channel' => 'apps.sync']);
    }

    /**
     * Sincronizar datos por viaje.
     */
    public function syncTrip(Request $request, $tripId)
    {
        $payload = $request->input('payload', []);
        $type = $request->input('type', 'update');
        $channel = 'trip.' . $tripId . '.sync';
        broadcast(new DataSync($channel, $payload, $type))->toOthers();
        return response()->json(['status' => 'ok', 'channel' => $channel]);
    }
}
