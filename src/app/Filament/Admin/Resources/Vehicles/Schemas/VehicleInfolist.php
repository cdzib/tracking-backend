<?php

namespace App\Filament\Admin\Resources\Vehicles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('plate'),
                TextEntry::make('capacity')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('driver_id')
                    ->numeric(),
                TextEntry::make('lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('lng')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
