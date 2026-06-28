<?php

namespace App\Enums\Promotion;

enum RewardType: string
{
    case PERCENTAGE = 'PERCENTAGE';
    case FIXED_AMOUNT = 'FIXED_AMOUNT';
    case FREE_PRODUCT = 'FREE_PRODUCT';
    case SPECIAL_PRICE = 'SPECIAL_PRICE';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Persentase Diskon',
            self::FIXED_AMOUNT => 'Nominal Tetap',
            self::FREE_PRODUCT => 'Produk Gratis',
            self::SPECIAL_PRICE => 'Harga Khusus',
        };
    }
}
