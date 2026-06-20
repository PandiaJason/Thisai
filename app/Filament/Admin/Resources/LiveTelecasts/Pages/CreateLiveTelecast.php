<?php

namespace App\Filament\Admin\Resources\LiveTelecasts\Pages;

use App\Filament\Admin\Resources\LiveTelecasts\LiveTelecastResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveTelecast extends CreateRecord
{
    protected static string $resource = LiveTelecastResource::class;
}
