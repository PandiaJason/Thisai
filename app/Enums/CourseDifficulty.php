<?php

namespace App\Enums;

enum CourseDifficulty: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::BEGINNER => 'Beginner',
            self::INTERMEDIATE => 'Intermediate',
            self::ADVANCED => 'Advanced',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BEGINNER => 'success',
            self::INTERMEDIATE => 'info',
            self::ADVANCED => 'danger',
        };
    }
}
