<?php

namespace App\Enums\Promotion;

enum MarginProtectionMode: string
{
    case BLOCK = 'BLOCK';
    case WARNING = 'WARNING';
    case ALLOW = 'ALLOW';

    public function label(): string
    {
        return match ($this) {
            self::BLOCK => 'Blokir Transaksi',
            self::WARNING => 'Tampilkan Warning',
            self::ALLOW => 'Izinkan',
        };
    }
}
