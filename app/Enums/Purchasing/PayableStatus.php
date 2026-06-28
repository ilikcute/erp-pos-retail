<?php

namespace App\Enums\Purchasing;

enum PayableStatus: string
{
    case OPEN = 'OPEN';
    case PARTIAL = 'PARTIAL';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Belum Dibayar',
            self::PARTIAL => 'Sebagian Dibayar',
            self::PAID => 'Lunas',
            self::OVERDUE => 'Jatuh Tempo',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
