<?php

namespace App\Filament\Admin\Resources\Bookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('trip_id')
                    ->numeric(),
                TextEntry::make('passenger_id')
                    ->numeric(),
                TextEntry::make('seat_num')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('qr_code')
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
