<?php

namespace App\Filament\Admin\Resources\QuestionBank\Pages;

use App\Filament\Admin\Resources\QuestionBank\QuestionBankResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
