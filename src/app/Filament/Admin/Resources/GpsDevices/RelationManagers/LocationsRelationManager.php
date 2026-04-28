<?php

namespace App\Filament\Admin\Resources\GpsDevices\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'locations';
    protected static ?string $title = 'Ubicaciones';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                //DateTimeColumn::make('recorded_at')->label('Fecha/Hora')->sortable(),
                TextColumn::make('latitude')->numeric(6),
                TextColumn::make('longitude')->numeric(6),
                TextColumn::make('speed')->numeric(1)->label('Velocidad'),
                TextColumn::make('course')->label('Rumbo'),
                TextColumn::make('satellites')->label('Sats'),
                TextColumn::make('battery_level')->label('Batería'),
            ])
            ->defaultSort('recorded_at', 'desc');
    }
}
