<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'category_code' => 'RETAIL',
                'category_name' => 'Pelanggan Retail',
                'description' => 'Pelanggan umum non-member / eceran',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'category_code' => 'WHOLESALE',
                'category_name' => 'Pelanggan Grosir',
                'description' => 'Pelanggan grosir / reseller',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'category_code' => 'VIP',
                'category_name' => 'Pelanggan VIP',
                'description' => 'Pelanggan loyal / member khusus',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('customer_categories')->insertOrIgnore($categories);

        $this->command->info('✅ Customer Categories berhasil di-seed.');
    }
}
