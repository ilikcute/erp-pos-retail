<?php

namespace App\Enums\Inventory;

enum LocationType: string
{
    case STORE_WAREHOUSE = 'STORE_WAREHOUSE';
    case WAREHOUSE = 'WAREHOUSE';
    case RACK = 'RACK';
    case DISPLAY = 'DISPLAY';
    case VIRTUAL = 'VIRTUAL'; // untuk transit/damaged

    public function isStockBearing(): bool
    {
        return in_array($this, [self::STORE_WAREHOUSE, self::WAREHOUSE]);
    }

    public function label(): string
    {
        return match ($this) {
            self::STORE_WAREHOUSE => 'Gudang Toko',
            self::WAREHOUSE => 'Gudang Pusat',
            self::RACK => 'Rak',
            self::DISPLAY => 'Display',
            self::VIRTUAL => 'Virtual',
        };
    }
}
