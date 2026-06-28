<?php

namespace App\Enums\POS;

enum SessionStatus: string
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Buka',
            self::CLOSED => 'Tutup',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'green',
            self::CLOSED => 'gray',
        };
    }
}
