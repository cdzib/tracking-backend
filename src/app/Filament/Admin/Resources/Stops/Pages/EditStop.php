<?php

namespace App\Filament\Admin\Resources\Stops\Pages;

use App\Filament\Admin\Resources\Stops\StopResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStop extends EditRecord
{
    protected static string $resource = StopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
