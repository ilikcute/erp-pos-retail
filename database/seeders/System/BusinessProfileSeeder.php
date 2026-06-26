<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessProfileSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('business_profiles')->insertOrIgnore([
            [
                'id' => 1,
                'business_name' => 'CV Retail Maju Bersama',
                'legal_name' => 'CV Retail Maju Bersama',
                'tax_id' => '01.234.567.8-901.000',
                'address' => 'Jl. Jenderal Sudirman No. 123',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
                'phone' => '021-5551234',
                'email' => 'info@retailmaju.co.id',
                'website' => 'https://retailmaju.co.id',
                'logo' => null,
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Business Profile berhasil di-seed.');
    }
}
