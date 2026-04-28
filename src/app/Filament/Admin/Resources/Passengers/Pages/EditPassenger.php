<?php

namespace App\Filament\Admin\Resources\Passengers\Pages;

use App\Filament\Admin\Resources\Passengers\PassengerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPassenger extends EditRecord
{
    protected static string $resource = PassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
