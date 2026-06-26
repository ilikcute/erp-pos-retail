<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitConversionSeeder extends Seeder
{
    public function run(): void
    {
        $pcsId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $boxId = DB::table('units')->where('unit_code', 'BOX')->value('id');
        $packId = DB::table('units')->where('unit_code', 'PACK')->value('id');
        $dusId = DB::table('units')->where('unit_code', 'DUS')->value('id');

        if ($pcsId && $boxId && $packId && $dusId) {
            $conversions = [
                [
                    'from_unit_id' => $boxId,
                    'to_unit_id' => $pcsId,
                    'conversion_factor' => 24.000000,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'from_unit_id' => $packId,
                    'to_unit_id' => $pcsId,
                    'conversion_factor' => 10.000000,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'from_unit_id' => $dusId,
                    'to_unit_id' => $boxId,
                    'conversion_factor' => 4.000000,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('unit_conversions')->insertOrIgnore($conversions);
        }

        $this->command->info('✅ Custom Unit Conversions (BOX/PACK/DUS) berhasil di-seed.');
    }
}
