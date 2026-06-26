<?php

namespace Database\Seeders\Performance;

use Database\Seeders\Performance\Concerns\SeedsPerformanceData;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class BulkSupplierSeeder extends Seeder
{
    use SeedsPerformanceData;

    public function run(): void
    {
        $count = $this->performanceCount();
        $faker = Faker::create('id_ID');

        $provinces = [
            'DKI Jakarta' => ['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Barat'],
            'Jawa Barat' => ['Bandung', 'Bekasi', 'Bogor'],
            'Jawa Timur' => ['Surabaya', 'Malang', 'Sidoarjo'],
            'Jawa Tengah' => ['Semarang', 'Solo', 'Magelang'],
            'Sumatera Utara' => ['Medan', 'Binjai', 'Deli Serdang'],
        ];

        $now = now();
        $rows = [];
        $provinceKeys = array_keys($provinces);

        for ($i = 1; $i <= $count; $i++) {
            $seq = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $province = $provinceKeys[($i - 1) % count($provinceKeys)];
            $cities = $provinces[$province];
            $city = $cities[($i - 1) % count($cities)];

            $rows[] = [
                'supplier_code' => "PERF-SUPP-{$seq}",
                'supplier_name' => 'PT '.$faker->company(),
                'contact_person' => $faker->name(),
                'phone' => '021-'.$faker->numerify('#######'),
                'email' => "supplier.perf{$i}@test.local",
                'address' => $faker->streetAddress(),
                'city' => $city,
                'province' => $province,
                'postal_code' => $faker->numerify('#####'),
                'tax_id' => $faker->numerify('##.###.###.#-###.###'),
                'payment_term_days' => [0, 7, 14, 30, 45][($i - 1) % 5],
                'credit_limit' => ($i % 10 + 1) * 10_000_000,
                'is_active' => $i % 25 !== 0,
                'notes' => "Dummy supplier performance test #{$i}",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertInChunks('suppliers', $rows);

        $this->command->info("✅ {$count} bulk suppliers (PERF-SUPP-*) berhasil di-seed.");
    }
}
