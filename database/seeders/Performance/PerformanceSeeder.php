<?php

namespace Database\Seeders\Performance;

use Illuminate\Database\Seeder;

/**
 * Seeder data dummy volume tinggi untuk performance testing REST API.
 *
 * Prasyarat: jalankan DatabaseSeeder terlebih dahulu.
 *
 * Usage:
 *   php artisan db:seed --class="Database\Seeders\Performance\PerformanceSeeder"
 *
 * Konfigurasi (opsional via .env):
 *   PERF_SEED_COUNT=500   — jumlah record per modul (minimum 500)
 *   PERF_SEED_CHUNK=100   — ukuran batch insert
 */
class PerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $count = max(500, (int) env('PERF_SEED_COUNT', 500));

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  Performance Data Seeder — REST API Testing');
        $this->command->info("  Target: {$count} records per modul");
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');

        $this->call([
            BulkCustomerSeeder::class,
            BulkSupplierSeeder::class,
            BulkProductSeeder::class,
            BulkSalesTransactionSeeder::class,
            BulkAuditLogSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Performance seeding selesai.');
        $this->command->info('');
        $this->command->table(
            ['Modul API', 'Endpoint', 'Prefix Data'],
            [
                ['MasterData', 'GET /api/v1/master-data/customers', 'PERF-CUST-*'],
                ['MasterData', 'GET /api/v1/master-data/suppliers', 'PERF-SUPP-*'],
                ['Product', 'GET /api/v1/product/products', 'PERF-PROD-* / SKU-PERF-*'],
                ['Pricing', 'GET /api/v1/pricing/price-lists/{id}/items', 'price_list_items'],
                ['POS', 'GET /api/v1/pos/transactions', 'PERF-POS-*'],
                ['System', 'GET /api/v1/system/audit-logs', 'PERF-DOC-*'],
            ]
        );
        $this->command->info('');
    }
}
