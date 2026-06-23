<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case DRAFT     = 'DRAFT';
    case PENDING   = 'PENDING';
    case APPROVED  = 'APPROVED';
    case POSTED    = 'POSTED';
    case CANCELLED = 'CANCELLED';
    case VOID      = 'VOID';
    case CLOSED    = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::PENDING   => 'Pending Approval',
            self::APPROVED  => 'Approved',
            self::POSTED    => 'Posted',
            self::CANCELLED => 'Cancelled',
            self::VOID      => 'Void',
            self::CLOSED    => 'Closed',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::POSTED, self::VOID, self::CLOSED]);
    }
}
