<?php

namespace App\Enums;

enum PriceListType: string
{
    case RETAIL = 'RETAIL';    // harga eceran default
    case WHOLESALE = 'WHOLESALE'; // harga grosir
    case SPECIAL = 'SPECIAL';   // harga khusus per customer category

    public function label(): string
    {
        return match ($this) {
            self::RETAIL => 'Harga Eceran',
            self::WHOLESALE => 'Harga Grosir',
            self::SPECIAL => 'Harga Khusus',
        };
    }
}
