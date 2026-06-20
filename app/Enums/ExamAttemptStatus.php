<?php

namespace App\Enums;

enum ExamAttemptStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case AUTO_SUBMITTED = 'auto_submitted';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In Progress',
            self::SUBMITTED => 'Submitted',
            self::AUTO_SUBMITTED => 'Auto Submitted',
        };
    }
}
