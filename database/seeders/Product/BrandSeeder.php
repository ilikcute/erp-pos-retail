<?php

namespace Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'id' => 1,
                'brand_code' => 'IND',
                'brand_name' => 'Indofood',
                'description' => 'Merek makanan dari PT Indofood',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'brand_code' => 'UNI',
                'brand_name' => 'Unilever',
                'description' => 'Merek kebutuhan harian dari PT Unilever',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'brand_code' => 'COC',
                'brand_name' => 'Coca-Cola',
                'description' => 'Merek minuman Coca-Cola Company',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('product_brands')->insertOrIgnore($brands);

        $this->command->info('✅ Product Brands berhasil di-seed.');
    }
}
