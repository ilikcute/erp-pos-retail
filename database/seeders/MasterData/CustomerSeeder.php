<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'id' => 1,
                'customer_code' => 'CUST-GENERAL',
                'customer_name' => 'Pelanggan Umum',
                'customer_category_id' => 1, // Retail
                'phone' => null,
                'email' => null,
                'address' => 'Jakarta',
                'city' => 'Jakarta',
                'birth_date' => null,
                'gender' => null,
                'tax_id' => null,
                'credit_limit' => 0.00,
                'is_active' => true,
                'notes' => 'Default customer untuk POS cash sales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_code' => 'CUST-0001',
                'customer_name' => 'Budi Santoso',
                'customer_category_id' => 2, // Wholesale
                'phone' => '081234567890',
                'email' => 'budi.santoso@email.com',
                'address' => 'Jl. Mangga Dua Raya No. 45',
                'city' => 'Jakarta Pusat',
                'birth_date' => '1990-05-15',
                'gender' => 'MALE',
                'tax_id' => '12.345.678.9-012.000',
                'credit_limit' => 5000000.00,
                'is_active' => true,
                'notes' => 'Pelanggan grosir rutin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'customer_code' => 'CUST-0002',
                'customer_name' => 'Siti Aminah',
                'customer_category_id' => 3, // VIP
                'phone' => '082345678901',
                'email' => 'siti.aminah@email.com',
                'address' => 'Jl. Kebon Jeruk No. 10',
                'city' => 'Jakarta Barat',
                'birth_date' => '1988-11-20',
                'gender' => 'FEMALE',
                'tax_id' => null,
                'credit_limit' => 10000000.00,
                'is_active' => true,
                'notes' => 'Pelanggan VIP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('customers')->insertOrIgnore($customers);

        $this->command->info('✅ Customers berhasil di-seed.');
    }
}
