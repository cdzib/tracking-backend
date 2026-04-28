<?php

namespace App\Filament\Admin\Resources\GpsDevices\Pages;

use App\Filament\Admin\Resources\GpsDevices\GpsDeviceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGpsDevice extends ViewRecord
{
    protected static string $resource = GpsDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
