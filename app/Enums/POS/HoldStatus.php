<?php

namespace App\Enums\POS;

enum HoldStatus: string
{
    case HELD      = 'HELD';
    case RESUMED   = 'RESUMED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::HELD      => 'On Hold',
            self::RESUMED   => 'Resumed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return $this === self::HELD;
    }
}
