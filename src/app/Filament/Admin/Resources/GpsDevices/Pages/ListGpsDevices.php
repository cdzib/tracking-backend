<?php

namespace App\Filament\Admin\Resources\GpsDevices\Pages;

use App\Filament\Admin\Resources\GpsDevices\GpsDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGpsDevices extends ListRecords
{
    protected static string $resource = GpsDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
