<?php

namespace App\Enums\Reporting;

enum ReportGroupBy: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
    case PRODUCT = 'product';
    case CATEGORY = 'category';
    case CASHIER = 'cashier';
    case CUSTOMER = 'customer';
    case PAYMENT_METHOD = 'payment_method';
    case LOCATION = 'location';

    public function label(): string
    {
        return match ($this) {
            self::DAY => 'Harian',
            self::WEEK => 'Mingguan',
            self::MONTH => 'Bulanan',
            self::YEAR => 'Tahunan',
            self::PRODUCT => 'Produk',
            self::CATEGORY => 'Kategori',
            self::CASHIER => 'Kasir',
            self::CUSTOMER => 'Pelanggan',
            self::PAYMENT_METHOD => 'Metode Pembayaran',
            self::LOCATION => 'Lokasi',
        };
    }
}
