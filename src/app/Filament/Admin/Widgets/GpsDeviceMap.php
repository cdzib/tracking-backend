<?php

namespace App\Filament\Admin\Widgets;

use App\Models\VehicleLocation;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class GpsDeviceMap extends MapWidget
{
    // use InteractsWithPageFilters;

    protected static ?string $heading = 'Ubicaciones del dispositivo';
    protected static ?bool $clustering = true;

    public ?int $deviceId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected int | string | array $columnSpan = 'full';
    public array $filters = [];

    protected $listeners = ['filters-updated' => '$refresh'];
    // -------------------------------------------------------
    // Obtiene los datos del mapa según los filtros activos
    // -------------------------------------------------------
    protected function getData(): array
    {
        // Leer filtros frescos del trait InteractsWithPageFilters
        $filters = $this->filters ?? [];

        $this->deviceId  = isset($filters['deviceId'])  ? (int) $filters['deviceId']  : null;
        $this->startDate = $filters['startDate'] ?? null;
        $this->endDate   = $filters['endDate']   ?? null;

        Log::info('GpsDeviceMap::getData()', [
            'deviceId'  => $this->deviceId,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);

        // ---- Con filtros: mostrar histórico del dispositivo ----
        if ($this->deviceId || $this->startDate || $this->endDate) {
            $locations = VehicleLocation::query()
                ->when($this->deviceId,  fn($q) => $q->where('gps_device_id', $this->deviceId))
                ->when($this->startDate, fn($q) => $q->where('recorded_at', '>=', Carbon::parse($this->startDate)->startOfDay()))
                ->when($this->endDate,   fn($q) => $q->where('recorded_at', '<=', Carbon::parse($this->endDate)->endOfDay()))
                ->orderBy('recorded_at')
                ->get();

            return $this->formatLocations($locations, showRoute: true);
        }

        // ---- Sin filtros: última ubicación de cada dispositivo ----
        $latestLocations = VehicleLocation::select('vehicle_locations.*')
            ->join(
                DB::raw('(
                    SELECT gps_device_id, MAX(recorded_at) as max_recorded_at
                    FROM vehicle_locations
                    GROUP BY gps_device_id
                ) as latest'),
                function ($join) {
                    $join->on('vehicle_locations.gps_device_id', '=', 'latest.gps_device_id')
                        ->on('vehicle_locations.recorded_at', '=', 'latest.max_recorded_at');
                }
            )
            ->get();

        return $this->formatLocations($latestLocations, showRoute: true);
    }

    // -------------------------------------------------------
    // Formatea los registros al array que espera el MapWidget
    // -------------------------------------------------------
    private function formatLocations($locations, bool $showRoute = false): array
    {
        $data  = [];
        $count = $locations->count();

        foreach ($locations as $index => $loc) {
            $isLatest = $showRoute && ($index === $count - 1);

            $data[] = [
                'location' => [
                    'lat' => (float) $loc->latitude,
                    'lng' => (float) $loc->longitude,
                ],
                'label'      => $loc->recorded_at ? $loc->recorded_at->toDateTimeString() : '',
                'icon'       => $isLatest
                    ? [
                        'url' => 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        'scaledSize' => ['width' => 40, 'height' => 40]
                    ]
                    : [
                        'url' => 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                        'scaledSize' => ['width' => 24, 'height' => 24]
                    ],
                'infoWindow' => $this->buildInfoWindow($loc, $isLatest),
            ];
        }

        return $data;
    }

    // -------------------------------------------------------
    // Construye el contenido del InfoWindow de cada marcador
    // -------------------------------------------------------
    private function buildInfoWindow($loc, bool $isLatest = false): string
    {
        $badge = $isLatest
            ? "<span style='color:#2563eb;font-weight:bold'>📍 Última posición</span>"
            : "📌 Histórico";

        return "
            <div style='font-family:sans-serif;min-width:180px;font-size:13px'>
                {$badge}<br><br>
                🕐 {$loc->recorded_at?->toDateTimeString()}<br>
                🚗 {$loc->speed} km/h<br>
                🔋 {$loc->battery_level}%<br>
                📡 {$loc->latitude}, {$loc->longitude}
            </div>
        ";
    }

    // -------------------------------------------------------
    // Configuración del widget
    // -------------------------------------------------------
    public function getColumns(): int | array
    {
        return 2;
    }

    public static function canView(): bool
    {
        return true;
    }

    public static function getDefaultColumnSpan(): int | string | null
    {
        return 'full';
    }

    // -------------------------------------------------------
    // Script para actualizaciones en tiempo real via Echo
    // -------------------------------------------------------
    public static function getScripts(): array
    {
        return [
            <<<JS
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof window.Echo === 'undefined') {
                    console.warn('Laravel Echo no está disponible.');
                    return;
                }

                window.Echo.channel('tracking.vehicles')
                    .listen('.location.updated', (e) => {
                        const { vehicle_id, location } = e;
                        if (!location || !location.lat || !location.lng) return;

                        if (typeof window.gpsMarkers === 'undefined') {
                            window.gpsMarkers = {};
                        }

                        const map = window.filamentGoogleMapsInstances?.[0]?.map;
                        if (!map) return;

                        if (window.gpsMarkers[vehicle_id]) {
                            window.gpsMarkers[vehicle_id].setPosition({
                                lat: location.lat,
                                lng: location.lng
                            });
                        } else {
                            window.gpsMarkers[vehicle_id] = new window.google.maps.Marker({
                                position: { lat: location.lat, lng: location.lng },
                                map: map,
                                label: vehicle_id.toString(),
                            });
                        }
                    });
            });
            JS
        ];
    }
    public function updatedFilters(): void
    {
        $this->resetMap();
    }

    public function resetMap(): void
    {
        $this->dispatch('filament-google-maps::refresh');
    }
}
