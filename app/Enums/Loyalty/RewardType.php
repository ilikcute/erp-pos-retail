<?php

namespace App\Enums\Loyalty;

enum RewardType: string
{
    case VOUCHER = 'VOUCHER';
    case PRODUCT = 'PRODUCT';
    case LUCKY_DRAW = 'LUCKY_DRAW';

    public function label(): string
    {
        return match ($this) {
            self::VOUCHER => 'Voucher',
            self::PRODUCT => 'Produk Fisik',
            self::LUCKY_DRAW => 'Undian',
        };
    }
}
