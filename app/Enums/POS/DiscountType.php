<?php

namespace App\Enums\POS;

enum DiscountType: string
{
    case MANUAL = 'MANUAL';
    case PROMO = 'PROMO';
    case VOUCHER = 'VOUCHER';
    case MEMBER = 'MEMBER';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual Discount',
            self::PROMO => 'Promotion',
            self::VOUCHER => 'Voucher',
            self::MEMBER => 'Member Discount',
        };
    }
}
