<?php

namespace Database\Seeders\POS;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'id' => 1,
                'method_code' => 'CASH',
                'method_name' => 'Tunai (Cash)',
                'method_type' => 'CASH',
                'account_id' => 1011, // Kas Utama
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'method_code' => 'QRIS',
                'method_name' => 'QRIS (Gopay/OVO/Dana)',
                'method_type' => 'QRIS',
                'account_id' => 1012, // Bank QRIS
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'method_code' => 'DEBIT_BCA',
                'method_name' => 'Debit BCA',
                'method_type' => 'DEBIT',
                'account_id' => 1013, // Bank BCA
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'method_code' => 'TRANSFER_MANDIRI',
                'name' => 'Transfer Mandiri',
                'type' => 'BANK_TRANSFER',
                'account_id' => 1014, // Bank Mandiri
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('payment_methods')->insertOrIgnore($methods);

        $this->command->info('✅ Payment Methods berhasil di-seed.');
    }
}
