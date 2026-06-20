<?php

namespace App\Filament\Faculty\Resources\CurrentAffairs\Pages;

use App\Filament\Faculty\Resources\CurrentAffairs\CurrentAffairsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrentAffairs extends EditRecord
{
    protected static string $resource = CurrentAffairsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
