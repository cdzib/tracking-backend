<?php

namespace App\Filament\Admin\Resources\Stops;

use App\Filament\Admin\Resources\Stops\Pages\CreateStop;
use App\Filament\Admin\Resources\Stops\Pages\EditStop;
use App\Filament\Admin\Resources\Stops\Pages\ListStops;
use App\Filament\Admin\Resources\Stops\Pages\ViewStop;
use App\Filament\Admin\Resources\Stops\Schemas\StopForm;
use App\Filament\Admin\Resources\Stops\Schemas\StopInfolist;
use App\Filament\Admin\Resources\Stops\Tables\StopsTable;
use App\Models\Stop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StopResource extends Resource
{
    protected static ?string $model = Stop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStopCircle;

    protected static ?string $recordTitleAttribute = 'Stop';

    public static function canViewAny(): bool
    {
        return false;
    }
    public static function form(Schema $schema): Schema
    {
        return StopForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StopInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StopsTable::configure($table);
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
            'index' => ListStops::route('/'),
            'create' => CreateStop::route('/create'),
            'view' => ViewStop::route('/{record}'),
            'edit' => EditStop::route('/{record}/edit'),
        ];
    }
}
