<?php

namespace App\Filament\Admin\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plate')
                    ->required(),
                TextInput::make('capacity')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['available' => 'Available', 'in_trip' => 'In trip', 'out_of_service' => 'Out of service'])
                    ->default('available')
                    ->required(),
                TextInput::make('driver_id')
                    ->required()
                    ->numeric(),
                TextInput::make('lat')
                    ->numeric(),
                TextInput::make('lng')
                    ->numeric(),
            ]);
    }
}
