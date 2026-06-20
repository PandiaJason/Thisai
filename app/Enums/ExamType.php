<?php

namespace App\Enums;

enum ExamType: string
{
    case DAILY_QUIZ = 'daily_quiz';
    case SECTION_TEST = 'section_test';
    case MOCK_TEST = 'mock_test';

    public function label(): string
    {
        return match ($this) {
            self::DAILY_QUIZ => 'Daily Quiz',
            self::SECTION_TEST => 'Section Test',
            self::MOCK_TEST => 'Full Mock Test',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DAILY_QUIZ => 'success',
            self::SECTION_TEST => 'warning',
            self::MOCK_TEST => 'danger',
        };
    }
}
