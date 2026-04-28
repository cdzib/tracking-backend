<?php

namespace App\Filament\Admin\Resources\Schedules;

use App\Filament\Admin\Resources\Schedules\Pages\CreateSchedule;
use App\Filament\Admin\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Admin\Resources\Schedules\Pages\ListSchedules;
use App\Filament\Admin\Resources\Schedules\Pages\ViewSchedule;
use App\Filament\Admin\Resources\Schedules\Schemas\ScheduleForm;
use App\Filament\Admin\Resources\Schedules\Schemas\ScheduleInfolist;
use App\Filament\Admin\Resources\Schedules\Tables\SchedulesTable;
use App\Models\Schedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static ?string $recordTitleAttribute = 'Schedule';

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
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
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'view' => ViewSchedule::route('/{record}'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
