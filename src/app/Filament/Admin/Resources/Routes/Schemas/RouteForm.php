<?php

namespace App\Filament\Admin\Resources\Routes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
