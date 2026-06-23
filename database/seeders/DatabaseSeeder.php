<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\System\RolePermissionSeeder;
use Database\Seeders\System\UserSeeder;
use Database\Seeders\System\SystemSettingSeeder;
use Database\Seeders\System\DocumentTypeSeeder;
use Database\Seeders\MasterData\TaxSeeder;
use Database\Seeders\MasterData\UnitSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan seeder WAJIB diikuti.
     * Dependency: RolePermission → User → Setting → DocumentType → Tax → Unit
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  POS-ERP Phase 1 Database Seeder');
        $this->command->info('═══════════════════════════════════════════');

        $this->call([
            // System
            RolePermissionSeeder::class,
            UserSeeder::class,
            SystemSettingSeeder::class,
            DocumentTypeSeeder::class,
            // MasterData
            TaxSeeder::class,
            UnitSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Phase 1 seeding selesai.');
        $this->command->info('');
    }
}
