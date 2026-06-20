<?php

namespace App\Filament\Admin\Resources\LiveTelecasts\Pages;

use App\Filament\Admin\Resources\LiveTelecasts\LiveTelecastResource;
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
