<?php

namespace App\Filament\Admin\Resources\Drivers;

use App\Filament\Admin\Resources\Drivers\Pages\CreateDriver;
use App\Filament\Admin\Resources\Drivers\Pages\EditDriver;
use App\Filament\Admin\Resources\Drivers\Pages\ListDrivers;
use App\Filament\Admin\Resources\Drivers\Pages\ViewDriver;
use App\Filament\Admin\Resources\Drivers\Schemas\DriverForm;
use App\Filament\Admin\Resources\Drivers\Schemas\DriverInfolist;
use App\Filament\Admin\Resources\Drivers\Tables\DriversTable;
use App\Models\Driver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $recordTitleAttribute = 'Driver';

    public static function form(Schema $schema): Schema
    {
        return DriverForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DriverInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriversTable::configure($table);
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
            'index' => ListDrivers::route('/'),
            'create' => CreateDriver::route('/create'),
            'view' => ViewDriver::route('/{record}'),
            'edit' => EditDriver::route('/{record}/edit'),
        ];
    }
}
