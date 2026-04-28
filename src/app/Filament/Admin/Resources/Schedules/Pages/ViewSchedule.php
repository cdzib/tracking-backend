<?php

namespace App\Filament\Admin\Resources\Schedules\Pages;

use App\Filament\Admin\Resources\Schedules\ScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSchedule extends ViewRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
