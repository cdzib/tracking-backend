<?php

namespace App\Filament\Admin\Resources\GpsDevices;

use App\Filament\Admin\Resources\GpsDevices\Pages\CreateGpsDevice;
use App\Filament\Admin\Resources\GpsDevices\Pages\EditGpsDevice;
use App\Filament\Admin\Resources\GpsDevices\Pages\ListGpsDevices;
use App\Filament\Admin\Resources\GpsDevices\Pages\ViewGpsDevice;
use App\Filament\Admin\Resources\GpsDevices\Schemas\GpsDeviceForm;
use App\Filament\Admin\Resources\GpsDevices\Schemas\GpsDeviceInfolist;
use App\Filament\Admin\Resources\GpsDevices\Tables\GpsDevicesTable;
use App\Models\GpsDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Admin\Resources\GpsDevices\RelationManagers\LocationsRelationManager;

class GpsDeviceResource extends Resource
{
    protected static ?string $model = GpsDevice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DevicePhoneMobile;

    protected static ?string $recordTitleAttribute = 'GpsDevice';

    public static function form(Schema $schema): Schema
    {
        return GpsDeviceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GpsDeviceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GpsDevicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LocationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGpsDevices::route('/'),
            'create' => CreateGpsDevice::route('/create'),
            'view' => ViewGpsDevice::route('/{record}'),
            'edit' => EditGpsDevice::route('/{record}/edit'),
        ];
    }
}
