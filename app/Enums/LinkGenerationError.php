<?php

namespace App\Enums;

enum LinkGenerationError: string
{
    case TIMEOUT = 'LINK_GENERATION_TIMEOUT';
    case DATABASE_FULL = 'LINK_GENERATION_DATABASE_FULL';

    public function label(): string
    {
        return match ($this) {
            self::TIMEOUT => __('links.errors.generation_timeout'),
            self::DATABASE_FULL => __('links.errors.database_full'),
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
