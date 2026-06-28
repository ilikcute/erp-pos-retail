<?php

namespace App\Enums\Purchasing;

enum InvoiceStatus: string
{
    case DRAFT = 'DRAFT';
    case POSTED = 'POSTED';
    case PARTIAL_PAID = 'PARTIAL_PAID';
    case PAID = 'PAID';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::POSTED => 'Diposting',
            self::PARTIAL_PAID => 'Sebagian Dibayar',
            self::PAID => 'Lunas',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
