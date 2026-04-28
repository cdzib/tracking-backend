<?php

namespace App\Filament\Admin\Resources\Routes\Relations;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class StopsRelationManager extends RelationManager
{
    protected static string $relationship = 'stops';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')->required(),
            Fieldset::make('Ubicación')
                ->schema([
                    Map::make('location')
                        ->label('Selecciona en el mapa')
                        ->default([20.9801, -89.6201])                       
                        ->autocomplete(true)
                        ->draggable(true)
                        ->clickable(true)
                        ->columnSpanFull(),
                    TextInput::make('lat')
                        ->label('Latitud')
                        ->required()
                        ->numeric(),
                    TextInput::make('lng')
                        ->label('Longitud')
                        ->required()
                        ->numeric(),
                ]),
            TextInput::make('order')->required()->numeric(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('lat'),
                Tables\Columns\TextColumn::make('lng'),
                Tables\Columns\TextColumn::make('order'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
