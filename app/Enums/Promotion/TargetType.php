<?php

namespace App\Enums\Promotion;

enum TargetType: string
{
    case ALL_PRODUCT = 'ALL_PRODUCT';
    case PRODUCT = 'PRODUCT';
    case CATEGORY = 'CATEGORY';

    public function label(): string
    {
        return match ($this) {
            self::ALL_PRODUCT => 'Semua Produk',
            self::PRODUCT => 'Produk Tertentu',
            self::CATEGORY => 'Kategori Tertentu',
        };
    }
}
