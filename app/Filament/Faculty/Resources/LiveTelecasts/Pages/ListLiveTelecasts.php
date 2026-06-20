<?php

namespace App\Filament\Faculty\Resources\LiveTelecasts\Pages;

use App\Filament\Faculty\Resources\LiveTelecasts\LiveTelecastResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiveTelecasts extends ListRecords
{
    protected static string $resource = LiveTelecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
