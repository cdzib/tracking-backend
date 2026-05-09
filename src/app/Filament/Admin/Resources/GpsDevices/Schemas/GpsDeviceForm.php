<?php

namespace App\Filament\Admin\Resources\GpsDevices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class GpsDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Map::make('location')
                    ->label('Ubicación')
                    ->defaultLocation([20.9674, -89.5926])
                    ->draggable()
                    ->clickable()
                    ->height('400px')
                    ->columnSpanFull()
                    ->afterStateHydrated(function (callable $set, $record) {
                        if ($record?->latitude && $record?->longitude) {
                            $set('location', [
                                'lat' => (float) $record->latitude,
                                'lng' => (float) $record->longitude,
                            ]);
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('latitude',  $state['lat']);
                        $set('longitude', $state['lng']);
                    })
                    ->dehydrated(false),
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'plate')
                    ->required(),
                TextInput::make('imei')
                    ->required(),
                TextInput::make('device_name')
                    ->required(),
                TextInput::make('device_model')
                    ->required()
                    ->default('Unknown'),
                TextInput::make('device_brand')
                    ->required()
                    ->default('Generic'),
                TextInput::make('latitude')
                    ->numeric()
                    ->readOnly(),

                TextInput::make('longitude')
                    ->numeric()
                    ->readOnly(),
                TextInput::make('altitude')
                    ->numeric(),
                TextInput::make('speed')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('course')
                    ->numeric(),
                TextInput::make('accuracy')
                    ->numeric(),
                DateTimePicker::make('last_update'),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'idle' => 'Idle',
                        'offline' => 'Offline',
                        'maintenance' => 'Maintenance',
                        'lost' => 'Lost',
                    ])
                    ->default('active')
                    ->required(),
                TextInput::make('battery_level')
                    ->numeric(),
                TextInput::make('signal_strength')
                    ->numeric(),
                TextInput::make('phone_number')
                    ->tel(),
                TextInput::make('sim_operator'),
                TextInput::make('voltage')
                    ->numeric(),
                TextInput::make('gps_update_interval')
                    ->required()
                    ->numeric()
                    ->default(30),
                TextInput::make('report_interval')
                    ->required()
                    ->numeric()
                    ->default(60),
                TextInput::make('config'),
                DateTimePicker::make('last_online'),
                DateTimePicker::make('first_seen'),
                DateTimePicker::make('last_command_sent'),
                TextInput::make('last_command'),
                TextInput::make('total_distance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('trips_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('error_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
