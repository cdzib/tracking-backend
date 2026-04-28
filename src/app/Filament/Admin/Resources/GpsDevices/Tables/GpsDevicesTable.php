<?php

namespace App\Filament\Admin\Resources\GpsDevices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GpsDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.plate')
                    ->searchable(),
                TextColumn::make('imei')
                    ->searchable(),
                TextColumn::make('device_name')
                    ->searchable(),
                TextColumn::make('device_model')
                    ->searchable(),
                TextColumn::make('device_brand')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('altitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('speed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('course')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accuracy')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_update')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('battery_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('signal_strength')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('sim_operator')
                    ->searchable(),
                TextColumn::make('voltage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gps_update_interval')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('report_interval')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_online')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('first_seen')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_command_sent')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_distance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trips_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('error_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
