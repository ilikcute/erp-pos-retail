<?php

namespace App\Enums\Accounting;

enum NormalBalance: string
{
    case DEBIT = 'DEBIT';
    case CREDIT = 'CREDIT';

    public function label(): string
    {
        return match ($this) {
            self::DEBIT => 'Debit',
            self::CREDIT => 'Kredit',
        };
    }
}
