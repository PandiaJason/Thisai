<?php

namespace App\Enums;

enum VideoStatus: string
{
    case PROCESSING = 'processing';
    case READY = 'ready';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PROCESSING => 'Processing',
            self::READY => 'Ready',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROCESSING => 'warning',
            self::READY => 'success',
            self::FAILED => 'danger',
        };
    }
}
