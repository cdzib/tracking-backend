<?php

namespace App\Filament\Admin\Resources\GpsDevices\Pages;

use App\Filament\Admin\Resources\GpsDevices\GpsDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGpsDevice extends EditRecord
{
    protected static string $resource = GpsDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
