<?php

namespace App\Enums\POS;

enum SessionStatus: string
{
    case OPEN   = 'OPEN';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::OPEN   => 'Open',
            self::CLOSED => 'Closed',
        };
    }

    public function isActive(): bool
    {
        return $this === self::OPEN;
    }
}
