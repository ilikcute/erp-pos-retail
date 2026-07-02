<?php

namespace App\Enums\Inventory;

enum LocationType: string
{
    case STORE_WAREHOUSE = 'STORE_WAREHOUSE';
    case RENTED_WAREHOUSE = 'RENTED_WAREHOUSE';
    case WAREHOUSE = 'WAREHOUSE';
    case RACK = 'RACK';
    case DISPLAY = 'DISPLAY';
    case RECEIVING = 'RECEIVING';
    case RETURN_AREA = 'RETURN_AREA';
    case DAMAGED_AREA = 'DAMAGED_AREA';
    case VIRTUAL = 'VIRTUAL';

    public function isStockBearing(): bool
    {
        return in_array($this, [
            self::STORE_WAREHOUSE,
            self::RENTED_WAREHOUSE,
            self::WAREHOUSE,
            self::RECEIVING,
            self::RETURN_AREA,
            self::DAMAGED_AREA,
        ]);
    }

    public function label(): string
    {
        return match ($this) {
            self::STORE_WAREHOUSE => 'Gudang Toko',
            self::RENTED_WAREHOUSE => 'Gudang Sewa Musiman',
            self::WAREHOUSE => 'Gudang Pusat',
            self::RACK => 'Rak Penyimpanan',
            self::DISPLAY => 'Rak Display',
            self::RECEIVING => 'Area Penerimaan',
            self::RETURN_AREA => 'Area Retur',
            self::DAMAGED_AREA => 'Area Barang Rusak',
            self::VIRTUAL => 'Virtual',
        };
    }
}
