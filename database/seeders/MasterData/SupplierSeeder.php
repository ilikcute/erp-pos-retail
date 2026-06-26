<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'id' => 1,
                'supplier_code' => 'SUPP-0001',
                'supplier_name' => 'PT Indofood Sukses Makmur',
                'contact_person' => 'Anto Wijaya',
                'phone' => '021-88887777',
                'email' => 'sales@indofood.com',
                'address' => 'Kawasan Industri Sudirman',
                'city' => 'Bekasi',
                'province' => 'Jawa Barat',
                'postal_code' => '17111',
                'tax_id' => '01.002.003.4-005.000',
                'payment_term_days' => 30,
                'credit_limit' => 50000000.00,
                'is_active' => true,
                'notes' => 'Pemasok produk Indomie, Sarimi, dll.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'supplier_code' => 'SUPP-0002',
                'supplier_name' => 'PT Unilever Indonesia Tbk',
                'contact_person' => 'Dewi Lestari',
                'phone' => '021-99998888',
                'email' => 'sales@unilever.com',
                'address' => 'BSD Green Office Park',
                'city' => 'Tangerang',
                'province' => 'Banten',
                'postal_code' => '15345',
                'tax_id' => '01.002.004.5-006.000',
                'payment_term_days' => 15,
                'credit_limit' => 100000000.00,
                'is_active' => true,
                'notes' => 'Pemasok sabun, shampoo, dll.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('suppliers')->insertOrIgnore($suppliers);

        $this->command->info('✅ Suppliers berhasil di-seed.');
    }
}
