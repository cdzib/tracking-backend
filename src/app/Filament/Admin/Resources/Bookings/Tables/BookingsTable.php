<?php

namespace App\Filament\Admin\Resources\Bookings\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trip_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trip.route.name')
                    ->label('Ruta'),
                TextColumn::make('trip.vehicle.plate')
                    ->label('Placa'),
                TextColumn::make('trip.vehicle.capacity')
                    ->label('Capacidad'),
                TextColumn::make('passenger.name')
                    ->label('Passenger')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seats')
                    ->label('Asientos Ocupados')
                    ->formatStateUsing(function ($state) {
                        $asientos = [];
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                // Si es un array de objetos
                                if (array_is_list($decoded)) {
                                    $asientos = collect($decoded)->pluck('seat')->all();
                                } elseif (isset($decoded['seat'])) {
                                    // Si es un solo objeto
                                    $asientos = [$decoded['seat']];
                                }
                            }
                        } elseif (is_array($state)) {
                            // Si es un array de objetos
                            if (array_is_list($state)) {
                                $asientos = collect($state)->pluck('seat')->all();
                            } elseif (isset($state['seat'])) {
                                // Si es un solo objeto
                                $asientos = [$state['seat']];
                            }
                        } elseif (is_object($state) && isset($state->seat)) {
                            $asientos = [$state->seat];
                        }
                        if (empty($asientos)) {
                            return '-';
                        }
                        return implode(', ', $asientos);
                    }),
                TextColumn::make('status')
                    ->badge(),
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
