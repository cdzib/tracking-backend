<?php

namespace App\Http\Controllers\Api;


use App\Models\GpsDevice;
use App\Models\VehicleLocation;
use App\Models\GpsEvent;
use App\Models\GpsCommand;
use App\Services\GpsProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GpsController
{
    public function __construct(private GpsProcessingService $gpsService) {}

     /**
     * POST /api/gps/device
     * Alta de dispositivo GPS
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'imei' => 'required|string|unique:gps_devices,imei',
            'device_name' => 'required|string',
            'device_model' => 'nullable|string',
            'device_brand' => 'nullable|string',
            'status' => 'nullable|string',
            'battery_level' => 'nullable|integer',
            'gps_update_interval' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $device = GpsDevice::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'GpsDevice creado',
            'data' => $device
        ], 201);
    }
    /**
     * POST /api/gps/update
     * Recibir actualización de ubicación del dispositivo
     */
    public function receiveUpdate(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'imei' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric|min:0',
            'course' => 'nullable|integer|between:0,360',
            'accuracy' => 'nullable|numeric',
            'satellites' => 'nullable|integer',
            'battery' => 'nullable|integer|between:0,100',
            'signal' => 'nullable|integer|between:0,31',
            'timestamp' => 'nullable|integer', // Unix timestamp
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $device = GpsDevice::where('imei', $request->imei)->firstOrFail();
            $device->markOnline();

            // Crear registro de ubicación
            $location = VehicleLocation::create([
                'vehicle_id' => $device->vehicle_id,
                'gps_device_id' => $device->id,
                'latitude' => (float)$request->latitude,
                'longitude' => (float)$request->longitude,
                'altitude' => $request->altitude,
                'speed' => (float)($request->speed ?? 0),
                'course' => $request->course,
                'accuracy' => $request->accuracy,
                'satellites' => $request->satellites,
                'battery_level' => $request->battery,
                'signal_strength' => $request->signal,
                'recorded_at' => $request->timestamp ? 
                    \Carbon\Carbon::createFromTimestamp($request->timestamp) : now(),
                'raw_data' => $request->all(),
            ]);

            // Actualizar dispositivo
            $device->update([
                'latitude' => (float)$request->latitude,
                'longitude' => (float)$request->longitude,
                'altitude' => $request->altitude,
                'speed' => (float)($request->speed ?? 0),
                'course' => $request->course,
                'accuracy' => $request->accuracy,
                'battery_level' => $request->battery,
                'signal_strength' => $request->signal,
                'last_update' => now(),
            ]);

            // Procesar datos (eventos, geofences, etc)
            $this->gpsService->processLocation($device, $location);

            // Broadcast actualización en tiempo real
            event(new \App\Events\LocationUpdated($device->vehicle, $location));

            return response()->json([
                'success' => true,
                'message' => 'Location updated',
                'data' => [
                    'location_id' => $location->id,
                    'device_id' => $device->id,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('GPS Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/gps/device/{imei}/status
     */
    public function getDeviceStatus($imei)
    {
        $device = GpsDevice::where('imei', $imei)
            ->with('vehicle', 'currentLocation')
            ->firstOrFail();

        return response()->json([
            'device_id' => $device->id,
            'imei' => $device->imei,
            'status' => $device->status,
            'battery' => $device->getBatteryStatus(),
            'location' => $device->currentLocation() ? [
                'latitude' => $device->currentLocation()->latitude,
                'longitude' => $device->currentLocation()->longitude,
                'speed' => $device->currentLocation()->speed,
                'updated_at' => $device->currentLocation()->recorded_at,
            ] : null,
            'pending_commands' => GpsCommand::where('gps_device_id', $device->id)
                ->where('status', 'pending')
                ->get(['id', 'command_type', 'parameters']),
        ]);
    }

    /**
     * POST /api/gps/device/{imei}/acknowledge
     * Confirmar recepción de datos
     */
    public function acknowledge($imei, Request $request)
    {
        $device = GpsDevice::where('imei', $imei)->firstOrFail();

        if ($request->has('command_id')) {
            $command = GpsCommand::findOrFail($request->command_id);
            $command->markAcknowledged($request->input('response'));
        }

        return response()->json(['success' => true]);
    }
}