<?php

namespace App\Filament\Admin\Resources\Passengers;

use App\Filament\Admin\Resources\Passengers\Pages\CreatePassenger;
use App\Filament\Admin\Resources\Passengers\Pages\EditPassenger;
use App\Filament\Admin\Resources\Passengers\Pages\ListPassengers;
use App\Filament\Admin\Resources\Passengers\Pages\ViewPassenger;
use App\Filament\Admin\Resources\Passengers\Schemas\PassengerForm;
use App\Filament\Admin\Resources\Passengers\Schemas\PassengerInfolist;
use App\Filament\Admin\Resources\Passengers\Tables\PassengersTable;
use App\Models\Passenger;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PassengerResource extends Resource
{
    protected static ?string $model = Passenger::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'Passenger';

    public static function form(Schema $schema): Schema
    {
        return PassengerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PassengerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PassengersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPassengers::route('/'),
            'create' => CreatePassenger::route('/create'),
            'view' => ViewPassenger::route('/{record}'),
            'edit' => EditPassenger::route('/{record}/edit'),
        ];
    }
}
