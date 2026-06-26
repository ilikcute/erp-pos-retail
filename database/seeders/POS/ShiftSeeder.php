<?php

namespace Database\Seeders\POS;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'id' => 1,
                'shift_code' => 'SHIFT-PAGI',
                'shift_name' => 'Shift Pagi',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'shift_code' => 'SHIFT-SIANG',
                'shift_name' => 'Shift Siang/Sore',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('shifts')->insertOrIgnore($shifts);

        $this->command->info('✅ Shifts berhasil di-seed.');
    }
}
