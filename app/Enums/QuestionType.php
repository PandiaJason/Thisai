<?php

namespace App\Enums;

enum QuestionType: string
{
    case SINGLE_CORRECT = 'single_correct';
    case MULTIPLE_CORRECT = 'multiple_correct';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_CORRECT => 'Single Correct MCQ',
            self::MULTIPLE_CORRECT => 'Multiple Correct MCQ',
        };
    }
}
