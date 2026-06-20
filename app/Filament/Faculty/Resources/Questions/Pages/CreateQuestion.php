<?php

namespace App\Filament\Faculty\Resources\Questions\Pages;

use App\Filament\Faculty\Resources\Questions\QuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;
}
