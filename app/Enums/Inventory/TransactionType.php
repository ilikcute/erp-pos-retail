<?php

namespace App\Enums\Inventory;

enum TransactionType: string
{
    case PURCHASE = 'PURCHASE';
    case RECEIPT = 'RECEIPT';
    case SALE = 'SALE';
    case TRANSFER_IN = 'TRANSFER_IN';
    case TRANSFER_OUT = 'TRANSFER_OUT';
    case ADJUSTMENT_IN = 'ADJUSTMENT_IN';
    case ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    case RETURN_IN = 'RETURN_IN';
    case RETURN_OUT = 'RETURN_OUT';
    case OPNAME = 'OPNAME';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Pembelian',
            self::RECEIPT => 'Penerimaan Barang',
            self::SALE => 'Penjualan',
            self::TRANSFER_IN => 'Transfer Masuk',
            self::TRANSFER_OUT => 'Transfer Keluar',
            self::ADJUSTMENT_IN => 'Penyesuaian Masuk',
            self::ADJUSTMENT_OUT => 'Penyesuaian Keluar',
            self::RETURN_IN => 'Retur Masuk',
            self::RETURN_OUT => 'Retur Keluar',
            self::OPNAME => 'Stock Opname',
        };
    }

    public function isIncrease(): bool
    {
        return in_array($this, [
            self::PURCHASE,
            self::RECEIPT,
            self::TRANSFER_IN,
            self::ADJUSTMENT_IN,
            self::RETURN_IN,
        ]);
    }
}
