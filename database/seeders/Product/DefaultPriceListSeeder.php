<?php

namespace Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultPriceListSeeder extends Seeder
{
    public function run(): void
    {
        $priceLists = [
            [
                'price_list_code' => 'RETAIL-DEFAULT',
                'price_list_name' => 'Harga Eceran Default',
                'price_list_type' => 'RETAIL',
                'currency'        => 'IDR',
                'is_default'      => true,
                'is_active'       => true,
                'valid_from'      => null,
                'valid_to'        => null,
                'description'     => 'Price list eceran default — digunakan POS jika tidak ada price list khusus.',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'price_list_code' => 'WHOLESALE-DEFAULT',
                'price_list_name' => 'Harga Grosir Default',
                'price_list_type' => 'WHOLESALE',
                'currency'        => 'IDR',
                'is_default'      => true,
                'is_active'       => true,
                'valid_from'      => null,
                'valid_to'        => null,
                'description'     => 'Price list grosir default.',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ];

        DB::table('price_lists')->insertOrIgnore($priceLists);

        $this->command->info('✅ Default price lists berhasil di-seed.');
    }
}
