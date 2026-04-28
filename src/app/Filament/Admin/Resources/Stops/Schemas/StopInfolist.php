<?php

namespace App\Filament\Admin\Resources\Stops\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StopInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('route_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('lat')
                    ->numeric(),
                TextEntry::make('lng')
                    ->numeric(),
                TextEntry::make('order')
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
