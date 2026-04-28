<?php

namespace App\Filament\Admin\Resources\Stops\Pages;

use App\Filament\Admin\Resources\Stops\StopResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStop extends ViewRecord
{
    protected static string $resource = StopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
