<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            [
                'tax_code'     => 'PPN11',
                'tax_name'     => 'PPN 11%',
                'tax_rate'     => 11.00,
                'is_inclusive' => false,
                'is_active'    => true,
            ],
            [
                'tax_code'     => 'PPN0',
                'tax_name'     => 'PPN 0% (Ekspor)',
                'tax_rate'     => 0.00,
                'is_inclusive' => false,
                'is_active'    => true,
            ],
            [
                'tax_code'     => 'BEBAS',
                'tax_name'     => 'Bebas Pajak',
                'tax_rate'     => 0.00,
                'is_inclusive' => false,
                'is_active'    => true,
            ],
        ];

        foreach ($taxes as &$tax) {
            $tax['created_at'] = now();
            $tax['updated_at'] = now();
        }

        DB::table('taxes')->insertOrIgnore($taxes);

        $this->command->info('✅ Default taxes berhasil di-seed.');
    }
}
