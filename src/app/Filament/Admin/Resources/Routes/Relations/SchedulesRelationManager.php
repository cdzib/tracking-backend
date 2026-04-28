<?php

namespace App\Filament\Admin\Resources\Routes\Relations;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';
    protected static ?string $recordTitleAttribute = 'departure_time';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TimePicker::make('departure_time')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('departure_time'),
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
