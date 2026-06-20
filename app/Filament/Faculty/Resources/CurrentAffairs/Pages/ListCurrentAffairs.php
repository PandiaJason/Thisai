<?php

namespace App\Filament\Faculty\Resources\CurrentAffairs\Pages;

use App\Filament\Faculty\Resources\CurrentAffairs\CurrentAffairsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCurrentAffairs extends ListRecords
{
    protected static string $resource = CurrentAffairsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
