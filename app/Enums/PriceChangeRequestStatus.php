<?php

namespace App\Enums;

enum PriceChangeRequestStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case APPLIED = 'APPLIED';   // sudah diterapkan ke price list
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::APPLIED => 'Diterapkan',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function canBeApproved(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeApplied(): bool
    {
        return $this === self::APPROVED;
    }
}
