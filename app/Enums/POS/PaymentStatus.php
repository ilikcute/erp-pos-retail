<?php

namespace App\Enums\POS;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case POSTED  = 'POSTED';
    case VOID    = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::POSTED  => 'Posted',
            self::VOID    => 'Void',
        };
    }
}
