<?php

namespace Tests\Feature\System;

use App\Enums\UserStatus;
use App\Models\System\Permission;
use App\Models\System\Role;
use App\Models\System\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebUserRoleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setupUserWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin-test'],
            [
                'display_name' => 'Admin Test',
                'is_active' => true,
            ]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

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

        return $user;
    }

    public function test_web_user_creation_logs_audit()
    {
        $admin = $this->setupUserWithPermissions(['system.user.manage']);

        $this->actingAs($admin);

        $response = $this->post('/system/users', [
            'name' => 'Web Created Staff',
            'email' => 'web-created@erp.com',
            'password' => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'status' => UserStatus::ACTIVE->value,
            'roles' => [],
        ]);

        $response->assertRedirect('/system/users');

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'CREATE_USER',
            'table_name' => 'users',
        ]);
    }

    public function test_web_user_update_logs_audit()
    {
        $admin = $this->setupUserWithPermissions(['system.user.manage']);
        $user = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->put("/system/users/{$user->id}", [
            'name' => 'Web Updated Name',
            'email' => $user->email,
            'status' => UserStatus::ACTIVE->value,
            'roles' => [],
        ]);

        $response->assertRedirect('/system/users');

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'UPDATE_USER',
            'table_name' => 'users',
            'record_id' => $user->id,
        ]);
    }

    public function test_web_user_deletion_logs_audit()
    {
        $admin = $this->setupUserWithPermissions(['system.user.manage']);
        $user = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->delete("/system/users/{$user->id}");

        $response->assertRedirect('/system/users');

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'DELETE_USER',
            'table_name' => 'users',
            'record_id' => $user->id,
        ]);
    }

    public function test_web_role_creation_logs_audit()
    {
        $admin = $this->setupUserWithPermissions(['system.role.manage']);

        $this->actingAs($admin);

        $response = $this->post('/system/roles', [
            'name' => 'web-role',
            'display_name' => 'Web Role',
            'description' => 'Web role description',
            'is_active' => true,
            'permissions' => [],
        ]);

        $response->assertRedirect('/system/roles');

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'CREATE_ROLE',
            'table_name' => 'roles',
        ]);
    }

    public function test_web_role_update_logs_audit()
    {
        $admin = $this->setupUserWithPermissions(['system.role.manage']);
        $role = Role::create([
            'name' => 'test-role-update',
            'display_name' => 'Test Role',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->put("/system/roles/{$role->id}", [
            'name' => 'test-role-update',
            'display_name' => 'Web Updated Role',
            'description' => 'Updated desc',
            'is_active' => true,
            'permissions' => [],
        ]);

        $response->assertRedirect('/system/roles');

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'UPDATE_ROLE',
            'table_name' => 'roles',
            'record_id' => $role->id,
        ]);
    }

    public function test_web_role_deletion_logs_audit()
    {
        $admin = $this->setupUserWithPermissions(['system.role.manage']);
        $role = Role::create([
            'name' => 'test-role-delete',
            'display_name' => 'Test Role',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->delete("/system/roles/{$role->id}");

        $response->assertRedirect('/system/roles');

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'DELETE_ROLE',
            'table_name' => 'roles',
            'record_id' => $role->id,
        ]);
    }

    public function test_web_user_creation_fails_if_more_than_one_role_is_passed()
    {
        $admin = $this->setupUserWithPermissions(['system.user.manage']);

        $this->actingAs($admin);

        $response = $this->post('/system/users', [
            'name' => 'Failed User',
            'email' => 'failed-user@erp.com',
            'password' => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'status' => UserStatus::ACTIVE->value,
            'roles' => [1, 2], // 2 roles!
        ]);

        $response->assertSessionHasErrors(['roles']);
    }
}
