<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\GpsDeviceMap;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    // use HasFiltersForm;

    protected string $view = 'filament.admin.pages.dashboard';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    // public function persistsFiltersInSession(): bool
    // {
    //     return true;
    // }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         FilterAction::make()
    //             ->schema([
    //                 DatePicker::make('startDate')
    //                     ->label('Fecha inicio')->default(now()->startOfDay()),
    //                 DatePicker::make('endDate')
    //                     ->label('Fecha fin')->default(now()->endOfDay()),
    //                 Select::make('deviceId')
    //                     ->label('Dispositivo')
    //                     ->options(
    //                         fn() => \App\Models\GpsDevice::query()
    //                             ->with('vehicle')
    //                             ->get()
    //                             ->mapWithKeys(fn($d) => [
    //                                 $d->id => $d->imei . ($d->vehicle ? " — {$d->vehicle->plate}" : '')
    //                             ])
    //                     )
    //                     ->searchable()
    //                     ->preload(),
    //             ]),
    //     ];
    // }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\StatsOverview::class,
            //GpsDeviceMap::class,
        ];
    }
}