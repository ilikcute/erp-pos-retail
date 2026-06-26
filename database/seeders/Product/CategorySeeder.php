<?php

namespace Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'category_code' => 'FD',
                'category_name' => 'Food (Makanan)',
                'parent_id' => null,
                'description' => 'Kategori makanan olahan dan instan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'category_code' => 'BV',
                'category_name' => 'Beverage (Minuman)',
                'parent_id' => null,
                'description' => 'Kategori minuman ringan dan jus',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'category_code' => 'PC',
                'category_name' => 'Personal Care',
                'parent_id' => null,
                'description' => 'Kategori sabun, shampoo, perawatan tubuh',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('product_categories')->insertOrIgnore($categories);

        $this->command->info('✅ Product Categories berhasil di-seed.');
    }
}
