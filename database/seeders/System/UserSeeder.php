<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Superadmin default ───────────────────────────────────────
        $userId = DB::table('users')->insertGetId([
            'name' => 'Super Administrator',
            'email' => 'superadmin@system.local',
            'password' => Hash::make('Admin@1234!'),
            'status' => 'ACTIVE',
            'force_password_change' => true,   // wajib ganti password saat login pertama
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign role superadmin
        $superadminRoleId = DB::table('roles')->where('name', 'superadmin')->value('id');

        DB::table('user_roles')->insertOrIgnore([
            'user_id' => $userId,
            'role_id' => $superadminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Superadmin dibuat: superadmin@system.local / Admin@1234!');
        $this->command->warn('⚠️  Segera ganti password default setelah login pertama.');
    }
}
