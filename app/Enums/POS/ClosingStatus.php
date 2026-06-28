<?php

namespace App\Enums\POS;

enum ClosingStatus: string
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';
    case LOCKED = 'LOCKED'; // Khusus untuk month closing

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Buka',
            self::CLOSED => 'Tutup',
            self::LOCKED => 'Terkunci',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'green',
            self::CLOSED => 'yellow',
            self::LOCKED => 'red',
        };
    }

    public function allowsTransaction(): bool
    {
        return $this === self::OPEN;
    }
}
