<?php

namespace App\Filament\Admin\Resources\Passengers\Pages;

use App\Filament\Admin\Resources\Passengers\PassengerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePassenger extends CreateRecord
{
    protected static string $resource = PassengerResource::class;
}
