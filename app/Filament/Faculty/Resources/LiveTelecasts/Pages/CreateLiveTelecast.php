<?php

namespace App\Filament\Faculty\Resources\LiveTelecasts\Pages;

use App\Filament\Faculty\Resources\LiveTelecasts\LiveTelecastResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveTelecast extends CreateRecord
{
    protected static string $resource = LiveTelecastResource::class;
}
