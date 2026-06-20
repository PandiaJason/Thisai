<?php

namespace App\Filament\Admin\Resources\LiveTelecasts\Pages;

use App\Filament\Admin\Resources\LiveTelecasts\LiveTelecastResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLiveTelecast extends EditRecord
{
    protected static string $resource = LiveTelecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
