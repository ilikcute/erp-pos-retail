<?php

namespace Database\Seeders\Performance;

use Database\Seeders\Performance\Concerns\SeedsPerformanceData;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkCustomerSeeder extends Seeder
{
    use SeedsPerformanceData;

    public function run(): void
    {
        $count = $this->performanceCount();
        $faker = Faker::create('id_ID');

        $categoryIds = DB::table('customer_categories')->pluck('id')->all();
        if ($categoryIds === []) {
            $this->command->warn('⚠️  Customer categories belum ada. Jalankan DatabaseSeeder terlebih dahulu.');

            return;
        }

        $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Denpasar', 'Yogyakarta', 'Malang'];
        $genders = ['MALE', 'FEMALE'];
        $now = now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $seq = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $name = $faker->name();

            $rows[] = [
                'customer_code' => "PERF-CUST-{$seq}",
                'customer_name' => $name,
                'customer_category_id' => $categoryIds[($i - 1) % count($categoryIds)],
                'phone' => '08'.$faker->numerify('##########'),
                'email' => strtolower(str_replace(' ', '.', $name))."{$i}@perf.test",
                'address' => $faker->streetAddress(),
                'city' => $cities[($i - 1) % count($cities)],
                'birth_date' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'gender' => $genders[$i % 2],
                'tax_id' => $i % 3 === 0 ? $faker->numerify('##.###.###.#-###.###') : null,
                'credit_limit' => ($i % 5) * 1_000_000,
                'is_active' => $i % 20 !== 0,
                'notes' => "Dummy customer performance test #{$i}",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertInChunks('customers', $rows);

        $this->command->info("✅ {$count} bulk customers (PERF-CUST-*) berhasil di-seed.");
    }
}
