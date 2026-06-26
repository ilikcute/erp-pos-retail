<?php

namespace Database\Seeders\POS;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSessionSeeder extends Seeder
{
    public function run(): void
    {
        $superadminId = DB::table('users')->where('email', 'superadmin@system.local')->value('id') ?? 1;

        $sessions = [
            [
                'id' => 1,
                'session_no' => 'SS-20260624-0001',
                'shift_id' => 1, // Shift Pagi
                'cashier_id' => $superadminId,
                'session_date' => '2026-06-24',
                'status' => 'CLOSED',
                'opening_cash' => 500000.00,
                'closing_cash' => 1250000.00,
                'expected_cash' => 1250000.00,
                'cash_difference' => 0.00,
                'total_sales' => 750000.00,
                'total_transactions' => 750000.00,
                'transaction_count' => 3,
                'notes' => 'Sesi kemarin lunas dan seimbang',
                'closed_at' => '2026-06-24 15:00:00',
                'created_at' => '2026-06-24 07:00:00',
                'updated_at' => '2026-06-24 15:00:00',
            ],
            [
                'id' => 2,
                'session_no' => 'SS-20260625-0001',
                'shift_id' => 1, // Shift Pagi
                'cashier_id' => $superadminId,
                'session_date' => '2026-06-25',
                'status' => 'OPEN',
                'opening_cash' => 500000.00,
                'closing_cash' => null,
                'expected_cash' => null,
                'cash_difference' => null,
                'total_sales' => 0.00,
                'total_transactions' => 0.00,
                'transaction_count' => 0,
                'notes' => 'Sesi hari ini aktif',
                'closed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('sales_sessions')->insertOrIgnore($sessions);

        $this->command->info('✅ Sales Sessions berhasil di-seed.');
    }
}
