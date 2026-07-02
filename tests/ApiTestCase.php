<?php

namespace Tests;

use App\Enums\UserStatus;
use App\Models\System\Permission;
use App\Models\System\Role;
use App\Models\System\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;

abstract class ApiTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper to create an active user, assign a role, seed permissions, and log in via Sanctum.
     */
    protected function actingAsUser(string $roleName = 'superadmin', array $permissions = []): User
    {
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        // Find or create role
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            [
                'display_name' => ucfirst($roleName),
                'is_active' => true,
            ]
        );

        // Ensure user is linked to role
        $user->roles()->syncWithoutDetaching([$role->id]);

        // Seed and sync permissions if provided
        foreach ($permissions as $permName) {
            $parts = explode('.', $permName);
            $module = $parts[0] ?? 'general';
            $resource = $parts[1] ?? 'general';
            $action = $parts[2] ?? 'general';

            $permission = Permission::firstOrCreate(
                ['name' => $permName],
                [
                    'module' => $module,
                    'resource' => $resource,
                    'action' => $action,
                    'display_name' => ucfirst($action).' '.ucfirst($resource),
                ]
            );

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        Sanctum::actingAs($user);

        return $user;
    }
}
