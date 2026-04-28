<?php

namespace App\Filament\Admin\Resources\Trips\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TripInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('van_id')
                    ->numeric(),
                TextEntry::make('route_id')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('datetime')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
