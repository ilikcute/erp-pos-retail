<?php

namespace App\Enums\Purchasing;

enum GRStatus: string
{
    case DRAFT = 'DRAFT';
    case POSTED = 'POSTED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::POSTED => 'Diposting',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
