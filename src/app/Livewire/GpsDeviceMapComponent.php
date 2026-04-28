<?php

namespace App\Livewire;

use App\Models\VehicleLocation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GpsDeviceMapComponent extends Component
{
    public ?int    $deviceId  = null;
    public ?string $startDate = null;
    public ?string $endDate   = null;

    public array $markers = [];

    public function mount(): void
    {
        $this->loadMarkers();
    }

    public function updated($property): void
    {
        if (in_array($property, ['deviceId', 'startDate', 'endDate'])) {
            $this->loadMarkers();
        }
    }

    public function loadMarkers(): void
    {
        $hasFilters = $this->deviceId || $this->startDate || $this->endDate;

        if ($hasFilters) {
            $locations = VehicleLocation::query()
                ->when($this->deviceId,  fn($q) => $q->where('gps_device_id', $this->deviceId))
                ->when($this->startDate, fn($q) => $q->where('recorded_at', '>=', Carbon::parse($this->startDate)->startOfDay()))
                ->when($this->endDate,   fn($q) => $q->where('recorded_at', '<=', Carbon::parse($this->endDate)->endOfDay()))
                ->orderBy('recorded_at')
                ->get();
        } else {
            $locations = VehicleLocation::select('vehicle_locations.*')
                ->join(
                    DB::raw('(
                        SELECT gps_device_id, MAX(recorded_at) as max_recorded_at
                        FROM vehicle_locations
                        GROUP BY gps_device_id
                    ) as latest'),
                    fn($join) => $join
                        ->on('vehicle_locations.gps_device_id', '=', 'latest.gps_device_id')
                        ->on('vehicle_locations.recorded_at',   '=', 'latest.max_recorded_at')
                )
                ->get();
        }

        $count = $locations->count();

        $this->markers = $locations->values()->map(function ($loc, $index) use ($count) {
            $isLatest = ($index === $count - 1);

            return [
                'lat'        => (float) $loc->latitude,
                'lng'        => (float) $loc->longitude,
                'label'      => $loc->recorded_at?->toDateTimeString() ?? '',
                'isLatest'   => $isLatest,
                'speed'      => $loc->speed,
                'battery'    => $loc->battery_level,
                'recordedAt' => $loc->recorded_at?->toDateTimeString(),
            ];
        })->toArray();

        // Notifica al JS que los markers cambiaron
        $this->dispatch('markers-updated', markers: $this->markers);
    }

    public function render()
    {
        return view('livewire.gps-device-map');
    }

    public function refreshMarkers(): void
    {
        $this->loadMarkers();
    }

    public function clearFilters(): void
    {
        $this->deviceId  = null;
        $this->startDate = null;
        $this->endDate   = null;

        $this->loadMarkers();
    }
}