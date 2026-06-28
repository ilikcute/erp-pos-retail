<?php

namespace App\Enums\Promotion;

enum ConditionType: string
{
    case MIN_AMOUNT = 'MIN_AMOUNT';
    case MIN_QTY = 'MIN_QTY';
    case DAY_OF_WEEK = 'DAY_OF_WEEK';
    case CUSTOMER_CATEGORY = 'CUSTOMER_CATEGORY';
    case PRODUCT = 'PRODUCT';
    case CATEGORY = 'CATEGORY';

    public function label(): string
    {
        return match ($this) {
            self::MIN_AMOUNT => 'Minimum Nominal',
            self::MIN_QTY => 'Minimum Quantity',
            self::DAY_OF_WEEK => 'Hari Tertentu',
            self::CUSTOMER_CATEGORY => 'Kategori Customer',
            self::PRODUCT => 'Produk Tertentu',
            self::CATEGORY => 'Kategori Produk',
        };
    }
}
