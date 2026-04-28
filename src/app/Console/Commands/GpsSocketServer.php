<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use App\Models\GpsDevice;
use App\Models\VehicleLocation;
use Illuminate\Console\Command;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Illuminate\Support\Facades\Log;

#[Signature('app:gps-socket-server  {--port=5055}')]
#[Description('Inicia servidor TCP para recibir datos GPS')]
class GpsSocketServer extends Command
{
    public function handle()
    {
        $port = $this->option('port');

        $loop = Loop::get();

        $socket = new SocketServer("0.0.0.0:$port", [], $loop);

        $socket->on('connection', function ($client) {
            $this->info("Conexión establecida: {$client->getRemoteAddress()}");

            $client->on('data', function ($data) use ($client) {
                $this->processGpsData($data, $client);
            });

            $client->on('end', function () use ($client) {
                $this->info("Conexión cerrada: {$client->getRemoteAddress()}");
            });

            $client->on('error', function ($error) {
                $this->error("Error: {$error->getMessage()}");
            });
        });

        $this->info("Servidor TCP escuchando en puerto $port");
        $loop->run();
    }

    private function processGpsData($data, $client)
    {
        // Parsear formato: $GPRMC,IMEI,LAT,LON,SPEED,COURSE,DATE,TIME,SATS,BATTERY#
        $data = trim($data, "#\n\r");
        $parts = explode(',', $data);

        Log::info('[GpsSocketServer] Datos recibidos', [
            'raw' => $data,
            'parts' => $parts,
        ]);

        if (count($parts) < 10 || $parts[0] !== '$GPRMC') {
            Log::warning('[GpsSocketServer] Formato inválido', ['raw' => $data]);
            return;
        }

        $imei = $parts[1];
        $latitude = (float)$parts[2];
        $longitude = (float)$parts[3];
        $speed = (float)$parts[4];
        $course = (int)$parts[5];
        $satellites = (int)$parts[8];
        $battery = (int)$parts[9];
        Log::info('[GpsSocketServer] Datos parseados', [
            'imei' => $imei,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'course' => $course,
            'satellites' => $satellites,
            'battery' => $battery,
        ]);
        try {
            $device = GpsDevice::where('imei', $imei)->firstOrFail();
            $device->markOnline();
            Log::info('[GpsSocketServer] Dispositivo encontrado', [
                'device' => $device->toArray(),
            ]);
            $location = $device->locations()->create([
                'vehicle_id' => $device->vehicle_id,
                'gps_device_id' => $device->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => $speed,
                'course' => $course,
                'satellites' => $satellites,
                'battery_level' => $battery,
                'recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $device->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => $speed,
                'battery_level' => $battery,
                'last_update' => now(),
            ]);

            Log::info('[GpsSocketServer] Emitiendo evento LocationUpdated', [
                'imei' => $imei,
                'vehicle_id' => $device->vehicle_id,
                'location_id' => $location->id,
            ]);

            // Confirmar recepción
            event(new \App\Events\LocationUpdated($device->vehicle, $location));
            event(new \App\Events\VehicleTracking($device->vehicle, $location, $device));
            $client->write("OK\n");
            $this->info("Datos procesados de IMEI: $imei");
        } catch (\Exception $e) {
            Log::error('[GpsSocketServer] Error procesando datos', [
                'error' => $e->getMessage(),
                'imei' => $imei ?? null,
                'raw' => $data,
            ]);
            $this->error("Error procesando datos: " . $e->getMessage());
            $client->write("ERROR\n");
        }
    }
}
