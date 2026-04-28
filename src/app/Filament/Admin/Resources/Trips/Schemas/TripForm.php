<?php

namespace App\Filament\Admin\Resources\Trips\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class TripForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('van_id')
                    ->relationship('vehicle', 'plate')
                    ->preload()
                    ->required(),
                Select::make('route_id')
                    ->relationship('route', 'name')
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending'      => 'Pendiente',
                        'assigned'     => 'Asignado',
                        'picking_up'   => 'Recogiendo pasajeros',
                        'on_route'     => 'En ruta',
                        'arrived'      => 'En sitio',
                        'completed'    => 'Finalizado',
                        'cancelled'    => 'Cancelado',
                    ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('datetime')
                    ->required(),
            ]);
    }
}
