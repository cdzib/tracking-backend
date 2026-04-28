<?php

namespace App\Filament\Admin\Resources\Stops\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('route_id')
                    ->relationship('route', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('lat')
                    ->required()
                    ->numeric(),
                TextInput::make('lng')
                    ->required()
                    ->numeric(),
                TextInput::make('order')
                    ->required()
                    ->numeric(),
            ]);
    }
}
