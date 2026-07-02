<?php

namespace App\Enums;

enum ProductType: string
{
    case SIMPLE = 'SIMPLE';   // produk tanpa varian
    case VARIANT = 'VARIANT';  // produk dengan varian (warna, ukuran, dll)
    case BUNDLE = 'BUNDLE';   // kumpulan produk dijual sebagai satu unit

    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'Produk Biasa',
            self::VARIANT => 'Produk dengan Varian',
            self::BUNDLE => 'Bundle / Paket',
        };
    }

    public function hasVariants(): bool
    {
        return $this === self::VARIANT;
    }
}
