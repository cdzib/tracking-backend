<?php

namespace App\Filament\Admin\Resources\Schedules\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('route_id')
                    ->required()
                    ->numeric(),
                TimePicker::make('departure_time')
                    ->required(),
            ]);
    }
}
