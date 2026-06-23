<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['unit_code' => 'PCS',  'unit_name' => 'Piece',       'description' => 'Satuan buah/unit'],
            ['unit_code' => 'BOX',  'unit_name' => 'Box',         'description' => 'Satuan kotak'],
            ['unit_code' => 'PACK', 'unit_name' => 'Pack',        'description' => 'Satuan pack/bungkus'],
            ['unit_code' => 'DUS',  'unit_name' => 'Dus',         'description' => 'Satuan dus/karton'],
            ['unit_code' => 'KG',   'unit_name' => 'Kilogram',    'description' => 'Satuan berat kilogram'],
            ['unit_code' => 'GR',   'unit_name' => 'Gram',        'description' => 'Satuan berat gram'],
            ['unit_code' => 'LTR',  'unit_name' => 'Liter',       'description' => 'Satuan volume liter'],
            ['unit_code' => 'ML',   'unit_name' => 'Mililiter',   'description' => 'Satuan volume mililiter'],
            ['unit_code' => 'MTR',  'unit_name' => 'Meter',       'description' => 'Satuan panjang meter'],
            ['unit_code' => 'CM',   'unit_name' => 'Centimeter',  'description' => 'Satuan panjang centimeter'],
            ['unit_code' => 'LUSIN', 'unit_name' => 'Lusin',       'description' => '12 buah'],
            ['unit_code' => 'KODI', 'unit_name' => 'Kodi',        'description' => '20 buah'],
        ];

        foreach ($units as &$unit) {
            $unit['is_active']  = true;
            $unit['created_at'] = now();
            $unit['updated_at'] = now();
        }

        DB::table('units')->insertOrIgnore($units);

        // Default conversions
        $pcsId  = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $lusinId = DB::table('units')->where('unit_code', 'LUSIN')->value('id');
        $kodiId  = DB::table('units')->where('unit_code', 'KODI')->value('id');
        $kgId   = DB::table('units')->where('unit_code', 'KG')->value('id');
        $grId   = DB::table('units')->where('unit_code', 'GR')->value('id');
        $ltrId  = DB::table('units')->where('unit_code', 'LTR')->value('id');
        $mlId   = DB::table('units')->where('unit_code', 'ML')->value('id');

        $conversions = [
            ['from_unit_id' => $lusinId, 'to_unit_id' => $pcsId,  'conversion_factor' => 12.000000],
            ['from_unit_id' => $kodiId,  'to_unit_id' => $pcsId,  'conversion_factor' => 20.000000],
            ['from_unit_id' => $kgId,    'to_unit_id' => $grId,   'conversion_factor' => 1000.000000],
            ['from_unit_id' => $ltrId,   'to_unit_id' => $mlId,   'conversion_factor' => 1000.000000],
        ];

        foreach ($conversions as &$c) {
            $c['is_active']  = true;
            $c['created_at'] = now();
            $c['updated_at'] = now();
        }

        DB::table('unit_conversions')->insertOrIgnore($conversions);

        $this->command->info('✅ Default units & conversions berhasil di-seed.');
    }
}
