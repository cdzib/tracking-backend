<?php

namespace App\Http\Controllers\Api;

use App\Models\GpsDevice;
use App\Models\GpsCommand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommandController
{
    /**
     * POST /api/tracking/devices/{deviceId}/send-command
     */
    public function sendCommand($deviceId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command_type' => 'required|in:update_interval,reboot,shutdown,get_status,get_location,set_geofence,custom',
            'parameters' => 'nullable|array',
        ]);

        $device = GpsDevice::findOrFail($deviceId);

        $command = $device->sendCommand(
            $validated['command_type'],
            $validated['parameters'] ?? []
        );

        // Aquí enviarías el comando al dispositivo (SMS, HTTP, etc)
        // sendCommandToDevice($device, $command);

        return response()->json([
            'success' => true,
            'message' => 'Command queued',
            'data' => [
                'command_id' => $command->id,
                'status' => $command->status,
                'command_type' => $command->command_type,
                'expires_at' => $command->expires_at,
            ]
        ], 201);
    }

    /**
     * GET /api/tracking/devices/{deviceId}/commands
     */
    public function listCommands($deviceId, Request $request): JsonResponse
    {
        $device = GpsDevice::findOrFail($deviceId);

        $query = $device->commands();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $commands = $query->latest()->limit(100)->get();

        return response()->json([
            'success' => true,
            'data' => $commands->map(fn($cmd) => [
                'id' => $cmd->id,
                'command_type' => $cmd->command_type,
                'parameters' => $cmd->parameters,
                'status' => $cmd->status,
                'response' => $cmd->response,
                'sent_at' => $cmd->sent_at,
                'acknowledged_at' => $cmd->acknowledged_at,
            ])
        ]);
    }
}