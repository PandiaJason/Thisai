<?php

namespace App\Filament\Admin\Resources\CurrentAffairs\Pages;

use App\Filament\Admin\Resources\CurrentAffairs\CurrentAffairsResource;
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
