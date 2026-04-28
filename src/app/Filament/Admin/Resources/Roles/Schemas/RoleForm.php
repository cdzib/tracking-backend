<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')->required(),
                \Filament\Forms\Components\Select::make('permissions')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload()
                    ->label('Permissions'),
            ]);
    }
}
