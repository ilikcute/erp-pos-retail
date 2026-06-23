<?php

namespace Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Product - Brand
            ['module' => 'product', 'resource' => 'brand',    'action' => 'view',   'display_name' => 'Lihat Brand'],
            ['module' => 'product', 'resource' => 'brand',    'action' => 'manage', 'display_name' => 'Kelola Brand'],
            // Product - Category
            ['module' => 'product', 'resource' => 'category', 'action' => 'view',   'display_name' => 'Lihat Kategori Produk'],
            ['module' => 'product', 'resource' => 'category', 'action' => 'manage', 'display_name' => 'Kelola Kategori Produk'],
            // Product - Product
            ['module' => 'product', 'resource' => 'product',  'action' => 'view',   'display_name' => 'Lihat Produk'],
            ['module' => 'product', 'resource' => 'product',  'action' => 'create', 'display_name' => 'Tambah Produk'],
            ['module' => 'product', 'resource' => 'product',  'action' => 'update', 'display_name' => 'Ubah Produk'],
            ['module' => 'product', 'resource' => 'product',  'action' => 'delete', 'display_name' => 'Hapus Produk'],
            // Pricing - Price List
            ['module' => 'pricing', 'resource' => 'price-list',            'action' => 'view',    'display_name' => 'Lihat Price List'],
            ['module' => 'pricing', 'resource' => 'price-list',            'action' => 'manage',  'display_name' => 'Kelola Price List'],
            // Pricing - Price Change Request
            ['module' => 'pricing', 'resource' => 'price-change-request',  'action' => 'view',    'display_name' => 'Lihat Price Change Request'],
            ['module' => 'pricing', 'resource' => 'price-change-request',  'action' => 'create',  'display_name' => 'Buat Price Change Request'],
            ['module' => 'pricing', 'resource' => 'price-change-request',  'action' => 'approve', 'display_name' => 'Setujui Price Change Request'],
            ['module' => 'pricing', 'resource' => 'price-change-request',  'action' => 'apply',   'display_name' => 'Terapkan Price Change Request'],
        ];

        foreach ($permissions as &$p) {
            $p['name']        = "{$p['module']}.{$p['resource']}.{$p['action']}";
            $p['description'] = null;
            $p['created_at']  = now();
            $p['updated_at']  = now();
        }

        DB::table('permissions')->insertOrIgnore($permissions);

        // Assign semua permission Phase 2 ke superadmin
        $superadminId  = DB::table('roles')->where('name', 'superadmin')->value('id');
        $adminId       = DB::table('roles')->where('name', 'admin')->value('id');
        $managerId     = DB::table('roles')->where('name', 'manager')->value('id');

        $phase2PermIds = DB::table('permissions')
            ->whereIn('module', ['product', 'pricing'])
            ->pluck('id');

        foreach ($phase2PermIds as $pid) {
            // Superadmin & admin: semua
            foreach ([$superadminId, $adminId] as $roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Manager: view + approve
        $managerPerms = DB::table('permissions')
            ->whereIn('module', ['product', 'pricing'])
            ->whereIn('action', ['view', 'approve'])
            ->pluck('id');

        foreach ($managerPerms as $pid) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $managerId,
                'permission_id' => $pid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Tambah document type untuk price change request
        DB::table('document_types')->insertOrIgnore([[
            'code'        => 'PRICE_CHANGE_REQUEST',
            'name'        => 'Price Change Request',
            'prefix'      => 'PCR',
            'suffix'      => null,
            'date_format' => 'Ym',
            'padding'     => 4,
            'separator'   => '-',
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]]);

        $this->command->info('✅ Phase 2 permissions & document type berhasil di-seed.');
    }
}
