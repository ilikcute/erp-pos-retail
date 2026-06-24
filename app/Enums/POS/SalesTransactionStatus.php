<?php

namespace App\Enums\POS;

enum SalesTransactionStatus: string
{
    case DRAFT = 'DRAFT';
    case POSTED = 'POSTED';
    case VOID  = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT  => 'Draft',
            self::POSTED => 'Posted',
            self::VOID   => 'Void',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::POSTED, self::VOID]);
    }
}
