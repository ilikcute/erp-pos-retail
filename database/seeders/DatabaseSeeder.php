<?php

namespace Database\Seeders;

use Database\Seeders\MasterData\CurrencySeeder;
use Database\Seeders\MasterData\CustomerCategorySeeder;
use Database\Seeders\MasterData\CustomerSeeder;
use Database\Seeders\MasterData\SupplierSeeder;
use Database\Seeders\MasterData\TaxSeeder;
use Database\Seeders\MasterData\UnitConversionSeeder;
use Database\Seeders\MasterData\UnitSeeder;
use Database\Seeders\POS\PaymentMethodSeeder;
use Database\Seeders\POS\SalesSessionSeeder;
use Database\Seeders\POS\SalesTransactionSeeder;
use Database\Seeders\POS\ShiftSeeder;
use Database\Seeders\Product\BrandSeeder;
use Database\Seeders\Product\CategorySeeder;
use Database\Seeders\Product\DefaultPriceListSeeder;
use Database\Seeders\Product\ProductPermissionSeeder;
use Database\Seeders\Product\ProductSeeder;
use Database\Seeders\System\BusinessProfileSeeder;
use Database\Seeders\System\DocumentTypeSeeder;
use Database\Seeders\System\RolePermissionSeeder;
use Database\Seeders\System\SystemSettingSeeder;
use Database\Seeders\System\UserSeeder;
use Database\Seeders\Performance\BulkAuditLogSeeder;
use Database\Seeders\Performance\BulkCustomerSeeder;
use Database\Seeders\Performance\BulkProductSeeder;
use Database\Seeders\Performance\BulkSalesTransactionSeeder;
use Database\Seeders\Performance\BulkSupplierSeeder;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan seeder WAJIB diikuti.
     * Dependency: RolePermission → User → Setting → DocumentType → BusinessProfile → Currency → Tax → Unit → Conversions → Customers/Suppliers → Brands/Categories → PriceLists → Products → Payments/Shifts/Sessions/Transactions
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  POS-ERP Database Seeder Complete');
        $this->command->info('═══════════════════════════════════════════');

        $this->call([
            // System
            RolePermissionSeeder::class,
            UserSeeder::class,
            SystemSettingSeeder::class,
            DocumentTypeSeeder::class,
            BusinessProfileSeeder::class,

            // MasterData
            CurrencySeeder::class,
            TaxSeeder::class,
            UnitSeeder::class,
            UnitConversionSeeder::class,
            CustomerCategorySeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,

            // Product & Pricing
            ProductPermissionSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            DefaultPriceListSeeder::class,
            ProductSeeder::class,

            // POS
            PaymentMethodSeeder::class,
            ShiftSeeder::class,
            SalesSessionSeeder::class,
            SalesTransactionSeeder::class,

            // Performance
            BulkCustomerSeeder::class,
            BulkSupplierSeeder::class,
            BulkProductSeeder::class,
            BulkSalesTransactionSeeder::class,
            BulkAuditLogSeeder::class,
            ReportMockDataSeeder::class,
            PurchasingMockDataSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Seluruh database seeding selesai dengan sukses.');
        $this->command->info('');
        $this->command->info('💡 Untuk data dummy performance testing (500+ record/API):');
        $this->command->info('   php artisan db:seed --class="Database\\Seeders\\Performance\\PerformanceSeeder"');
        $this->command->info('');
    }
}
