<?php

namespace App\Enums;

enum LinkGenerationError: string
{
    case TIMEOUT = 'LINK_GENERATION_TIMEOUT';
    case DATABASE_FULL = 'LINK_GENERATION_DATABASE_FULL';

    public function label(): string
    {
        return match ($this) {
            self::TIMEOUT => 'Превышено время ожидания генерации ссылки.',
            self::DATABASE_FULL => 'Свободные коды закончились. База данных переполнена.',
        };
    }

    public function httpStatus(): int
    {
        return match ($this) {
            self::TIMEOUT => 408,       // Request Timeout
            self::DATABASE_FULL => 507, // Insufficient Storage
        };
    }
}
