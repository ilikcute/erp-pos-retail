<?php

namespace App\Enums\Purchasing;

enum POStatus: string
{
    case DRAFT = 'DRAFT';
    case APPROVED = 'APPROVED';
    case PARTIAL_RECEIVED = 'PARTIAL_RECEIVED';
    case RECEIVED = 'RECEIVED';
    case CANCELLED = 'CANCELLED';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Disetujui',
            self::PARTIAL_RECEIVED => 'Sebagian Diterima',
            self::RECEIVED => 'Diterima',
            self::CANCELLED => 'Dibatalkan',
            self::CLOSED => 'Ditutup',
        };
    }
}
