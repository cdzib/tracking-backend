<?php

namespace App\Filament\Admin\Resources\Passengers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class PassengerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Password')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
            ]);
    }
}
