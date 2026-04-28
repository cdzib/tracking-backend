<?php

namespace App\Filament\Admin\Resources\Bookings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('trip_id')
                    ->relationship('trip', 'id')
                    ->preload()
                    ->required(),
                Select::make('passenger_id')
                    ->relationship('passenger', 'name')
                    ->preload()
                    ->required(),
                TextInput::make('seat_num')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['active' => 'Active', 'cancelled' => 'Cancelled', 'used' => 'Used'])
                    ->default('active')
                    ->required(),
                TextInput::make('qr_code'),
            ]);
    }
}
