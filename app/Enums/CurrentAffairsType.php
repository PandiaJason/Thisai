<?php

namespace App\Enums;

enum CurrentAffairsType: string
{
    case DAILY = 'daily';
    case EDITORIAL = 'editorial';
    case PIB = 'pib';
    case SCHEME = 'scheme';
    case FACT = 'fact';

    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Daily Current Affairs',
            self::EDITORIAL => 'Editorial Analysis',
            self::PIB => 'PIB Notes',
            self::SCHEME => 'Government Schemes',
            self::FACT => 'Important Facts',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DAILY => 'heroicon-o-calendar',
            self::EDITORIAL => 'heroicon-o-document-text',
            self::PIB => 'heroicon-o-megaphone',
            self::SCHEME => 'heroicon-o-academic-cap',
            self::FACT => 'heroicon-o-light-bulb',
        };
    }
}
