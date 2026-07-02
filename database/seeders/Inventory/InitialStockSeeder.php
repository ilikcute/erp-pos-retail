<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialStockSeeder extends Seeder
{
    /**
     * Seed stok awal untuk semua variant produk aktif.
     *
     * Logika:
     * - Hanya insert variant yang BELUM punya data di inventory_balances
     * - Stok awal default: 50 unit (bisa diubah via $defaultQty)
     * - Location: ambil dari inventory_locations yang aktif (WAREHOUSE/STORE_WAREHOUSE)
     */
    public function run(): void
    {
        $defaultQty = 50; // Stok awal default per variant

        // Ambil semua lokasi aktif yang menampung stok
        $locations = DB::table('inventory_locations')
            ->where('is_active', true)
            ->whereIn('type', ['WAREHOUSE', 'STORE_WAREHOUSE', 'STORE'])
            ->pluck('id');

        if ($locations->isEmpty()) {
            // Fallback: ambil semua lokasi aktif
            $locations = DB::table('inventory_locations')
                ->where('is_active', true)
                ->pluck('id');
        }

        if ($locations->isEmpty()) {
            $this->command->error('❌ Tidak ada lokasi inventory aktif. Seeder dibatalkan.');

            return;
        }

        // Ambil semua variant produk aktif
        $variants = DB::table('product_variants')
            ->where('is_active', true)
            ->select('id', 'product_id', 'sku')
            ->get();

        if ($variants->isEmpty()) {
            $this->command->error('❌ Tidak ada variant produk aktif ditemukan.');

            return;
        }

        $this->command->info('📦 Memulai seeding stok awal...');
        $this->command->info("   Jumlah variant aktif : {$variants->count()}");
        $this->command->info("   Jumlah lokasi        : {$locations->count()}");
        $this->command->info("   Stok awal per variant: {$defaultQty} unit");
        $this->command->newLine();

        $now = now();
        $inserted = 0;
        $skipped = 0;

        // Ambil semua kombinasi yang sudah ada sekaligus (efisien, hindari N+1 query)
        $existing = DB::table('inventory_balances')
            ->select('product_variant_id', 'location_id')
            ->get()
            ->mapWithKeys(fn ($row) => ["{$row->product_variant_id}_{$row->location_id}" => true]);

        $inserts = [];

        foreach ($locations as $locationId) {
            foreach ($variants as $variant) {
                $key = "{$variant->id}_{$locationId}";

                if ($existing->has($key)) {
                    $skipped++;

                    continue;
                }

                $inserts[] = [
                    'product_variant_id' => $variant->id,
                    'location_id' => $locationId,
                    'qty_on_hand' => $defaultQty,
                    'qty_reserved' => 0,
                    'qty_available' => $defaultQty,
                    'last_movement_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $inserted++;

                // Batch insert setiap 200 record agar tidak timeout
                if (count($inserts) >= 200) {
                    DB::table('inventory_balances')->insert($inserts);
                    $inserts = [];
                    $this->command->info('   ✅ Batch inserted 200 records...');
                }
            }
        }

        // Insert sisa yang belum di-batch
        if (! empty($inserts)) {
            DB::table('inventory_balances')->insert($inserts);
        }

        $this->command->newLine();
        $this->command->info('✅ Seeding stok awal selesai!');
        $this->command->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Variant × Lokasi diproses', $variants->count() * $locations->count()],
                ['Record baru diinsert', $inserted],
                ['Sudah ada (dilewati)', $skipped],
            ]
        );
    }
}
