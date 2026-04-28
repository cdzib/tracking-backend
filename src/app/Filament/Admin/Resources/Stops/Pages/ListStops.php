<?php

namespace App\Filament\Admin\Resources\Stops\Pages;

use App\Filament\Admin\Resources\Stops\StopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStops extends ListRecords
{
    protected static string $resource = StopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
