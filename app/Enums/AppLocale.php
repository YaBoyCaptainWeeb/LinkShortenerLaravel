<?php

namespace App\Enums;

enum AppLocale: string
{
    case English = 'en';
    case Russian = 'ru';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
