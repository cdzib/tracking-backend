<?php

namespace App\Filament\Admin\Resources\Routes;

use App\Filament\Admin\Resources\Routes\Pages\CreateRoute;
use App\Filament\Admin\Resources\Routes\Pages\EditRoute;
use App\Filament\Admin\Resources\Routes\Pages\ListRoutes;
use App\Filament\Admin\Resources\Routes\Pages\ViewRoute;
use App\Filament\Admin\Resources\Routes\Schemas\RouteForm;
use App\Filament\Admin\Resources\Routes\Schemas\RouteInfolist;
use App\Filament\Admin\Resources\Routes\Tables\RoutesTable;
use App\Models\Route;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RouteResource extends Resource
{
    protected static ?string $model = Route::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'Route';

    public static function form(Schema $schema): Schema
    {
        return RouteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RouteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoutesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\Routes\Relations\StopsRelationManager::class,
            \App\Filament\Admin\Resources\Routes\Relations\SchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoutes::route('/'),
            'create' => CreateRoute::route('/create'),
            'view' => ViewRoute::route('/{record}'),
            'edit' => EditRoute::route('/{record}/edit'),
        ];
    }
}
