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
                'code' => 'CASH',
                'name' => 'Tunai (Cash)',
                'type' => 'CASH',
                'account_id' => 1011, // Kas Utama
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'Pembayaran tunai/cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'QRIS',
                'name' => 'QRIS (Gopay/OVO/Dana)',
                'type' => 'QRIS',
                'account_id' => 1012, // Bank QRIS
                'is_active' => true,
                'sort_order' => 2,
                'description' => 'Pembayaran scan QRIS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'code' => 'DEBIT_BCA',
                'name' => 'Debit BCA',
                'type' => 'DEBIT',
                'account_id' => 1013, // Bank BCA
                'is_active' => true,
                'sort_order' => 3,
                'description' => 'Pembayaran kartu debit BCA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'code' => 'TRANSFER_MANDIRI',
                'name' => 'Transfer Mandiri',
                'type' => 'BANK_TRANSFER',
                'account_id' => 1014, // Bank Mandiri
                'is_active' => true,
                'sort_order' => 4,
                'description' => 'Transfer bank Mandiri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('payment_methods')->insertOrIgnore($methods);

        $this->command->info('✅ Payment Methods berhasil di-seed.');
    }
}
