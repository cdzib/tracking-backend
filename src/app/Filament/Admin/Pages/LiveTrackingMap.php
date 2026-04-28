<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class LiveTrackingMap extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;
    protected static ?string $title = 'Tracking en Tiempo Real';
    protected string $view = 'filament.admin.pages.live-tracking-map';

}
