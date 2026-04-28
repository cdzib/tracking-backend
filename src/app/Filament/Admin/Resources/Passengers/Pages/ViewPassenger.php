<?php

namespace App\Filament\Admin\Resources\Passengers\Pages;

use App\Filament\Admin\Resources\Passengers\PassengerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPassenger extends ViewRecord
{
    protected static string $resource = PassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
