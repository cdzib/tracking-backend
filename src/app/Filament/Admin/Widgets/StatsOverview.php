<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Vehículos', \App\Models\Vehicle::count())
                ->description('Total de vehículos')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),

            Stat::make('Vehículos activos', \App\Models\Vehicle::where('status', 'active')->count())
                ->description('Total de vehículos activos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('Dispositivos GPS', \App\Models\GpsDevice::count())
                ->description('Total de dispositivos GPS')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make('Eventos', \App\Models\GpsEvent::count())
                ->description('Total de eventos')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('warning'),

            Stat::make('Viajes', \App\Models\Trip::count())
                ->description('Total de viajes')
                ->descriptionIcon('heroicon-m-map')
                ->color('success'),

            Stat::make('Pasajeros', \App\Models\Passenger::count())
                ->description('Total de pasajeros')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Conductores', \App\Models\Driver::count())
                ->description('Total de conductores')
                ->descriptionIcon('heroicon-m-identification')
                ->color('info'),

            Stat::make('Reservas', \App\Models\Booking::count())
                ->description('Total de reservas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
