<?php

namespace App\Filament\Admin\Resources\GpsDevices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GpsDeviceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('vehicle.id')
                    ->label('Vehicle'),
                TextEntry::make('imei'),
                TextEntry::make('device_name'),
                TextEntry::make('device_model'),
                TextEntry::make('device_brand'),
                TextEntry::make('latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('altitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('speed')
                    ->numeric(),
                TextEntry::make('course')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('accuracy')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('last_update')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('battery_level')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('signal_strength')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('phone_number')
                    ->placeholder('-'),
                TextEntry::make('sim_operator')
                    ->placeholder('-'),
                TextEntry::make('voltage')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('gps_update_interval')
                    ->numeric(),
                TextEntry::make('report_interval')
                    ->numeric(),
                TextEntry::make('last_online')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('first_seen')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_command_sent')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('total_distance')
                    ->numeric(),
                TextEntry::make('trips_count')
                    ->numeric(),
                TextEntry::make('error_count')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
