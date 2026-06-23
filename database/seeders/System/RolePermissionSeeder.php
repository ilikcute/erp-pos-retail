<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permissions Phase 1 ──────────────────────────────────────
        $permissions = [
            // System - User
            ['module' => 'system', 'resource' => 'user',    'action' => 'view',   'display_name' => 'Lihat User'],
            ['module' => 'system', 'resource' => 'user',    'action' => 'manage', 'display_name' => 'Kelola User'],
            // System - Role
            ['module' => 'system', 'resource' => 'role',    'action' => 'view',   'display_name' => 'Lihat Role'],
            ['module' => 'system', 'resource' => 'role',    'action' => 'manage', 'display_name' => 'Kelola Role'],
            // System - Setting
            ['module' => 'system', 'resource' => 'setting', 'action' => 'view',   'display_name' => 'Lihat Setting'],
            ['module' => 'system', 'resource' => 'setting', 'action' => 'manage', 'display_name' => 'Kelola Setting'],
            // System - Audit
            ['module' => 'system', 'resource' => 'audit',   'action' => 'view',   'display_name' => 'Lihat Audit Log'],
            // MasterData - Supplier
            ['module' => 'master-data', 'resource' => 'supplier', 'action' => 'view',   'display_name' => 'Lihat Supplier'],
            ['module' => 'master-data', 'resource' => 'supplier', 'action' => 'create', 'display_name' => 'Tambah Supplier'],
            ['module' => 'master-data', 'resource' => 'supplier', 'action' => 'update', 'display_name' => 'Ubah Supplier'],
            ['module' => 'master-data', 'resource' => 'supplier', 'action' => 'delete', 'display_name' => 'Hapus Supplier'],
            // MasterData - Customer
            ['module' => 'master-data', 'resource' => 'customer', 'action' => 'view',   'display_name' => 'Lihat Customer'],
            ['module' => 'master-data', 'resource' => 'customer', 'action' => 'create', 'display_name' => 'Tambah Customer'],
            ['module' => 'master-data', 'resource' => 'customer', 'action' => 'update', 'display_name' => 'Ubah Customer'],
            ['module' => 'master-data', 'resource' => 'customer', 'action' => 'delete', 'display_name' => 'Hapus Customer'],
            // MasterData - Unit
            ['module' => 'master-data', 'resource' => 'unit', 'action' => 'view',   'display_name' => 'Lihat Unit'],
            ['module' => 'master-data', 'resource' => 'unit', 'action' => 'manage', 'display_name' => 'Kelola Unit'],
            // MasterData - Tax
            ['module' => 'master-data', 'resource' => 'tax', 'action' => 'view',   'display_name' => 'Lihat Pajak'],
            ['module' => 'master-data', 'resource' => 'tax', 'action' => 'manage', 'display_name' => 'Kelola Pajak'],
        ];

        foreach ($permissions as &$p) {
            $p['name']       = "{$p['module']}.{$p['resource']}.{$p['action']}";
            $p['description'] = null;
            $p['created_at'] = now();
            $p['updated_at'] = now();
        }

        DB::table('permissions')->insertOrIgnore($permissions);

        // ── Default Roles ────────────────────────────────────────────
        $roles = [
            ['name' => 'superadmin', 'display_name' => 'Super Administrator', 'description' => 'Akses penuh ke semua modul'],
            ['name' => 'admin',      'display_name' => 'Administrator',        'description' => 'Akses admin sistem'],
            ['name' => 'kasir',      'display_name' => 'Kasir',                'description' => 'Operator kasir POS'],
            ['name' => 'gudang',     'display_name' => 'Staff Gudang',         'description' => 'Operator inventory'],
            ['name' => 'purchasing', 'display_name' => 'Staff Purchasing',     'description' => 'Operator purchasing'],
            ['name' => 'accounting', 'display_name' => 'Staff Akuntansi',      'description' => 'Operator accounting'],
            ['name' => 'manager',    'display_name' => 'Manager',              'description' => 'Manajemen & approval'],
        ];

        foreach ($roles as &$r) {
            $r['is_active']  = true;
            $r['created_at'] = now();
            $r['updated_at'] = now();
        }

        DB::table('roles')->insertOrIgnore($roles);

        // ── Assign semua permissions ke superadmin ───────────────────
        $superadminId  = DB::table('roles')->where('name', 'superadmin')->value('id');
        $adminId       = DB::table('roles')->where('name', 'admin')->value('id');
        $permissionIds = DB::table('permissions')->pluck('id');

        // Superadmin: semua permissions
        foreach ($permissionIds as $pid) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id'       => $superadminId,
                'permission_id' => $pid,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Admin: semua permissions kecuali system.setting.manage
        $adminPermissions = DB::table('permissions')
            ->where('name', '!=', 'system.setting.manage')
            ->pluck('id');

        foreach ($adminPermissions as $pid) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id'       => $adminId,
                'permission_id' => $pid,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
