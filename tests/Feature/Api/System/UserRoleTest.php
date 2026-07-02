<?php

namespace Tests\Feature\Api\System;

use App\Enums\UserStatus;
use App\Models\System\Permission;
use App\Models\System\User;
use Tests\ApiTestCase;

class UserRoleTest extends ApiTestCase
{
    public function test_user_without_permission_cannot_access_user_list()
    {
        $this->actingAsUser('kasir', []); // No permissions

        $response = $this->getJson('/api/v1/system/users');

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_access_user_list()
    {
        $this->actingAsUser('admin', ['system.user.view']);

        $response = $this->getJson('/api/v1/system/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_create_user()
    {
        $this->actingAsUser('admin', ['system.user.manage']);

        // Check if there is a store user validation
        $response = $this->postJson('/api/v1/system/users', [
            'name' => 'New Staff',
            'email' => 'new-staff@erp.com',
            'password' => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'status' => UserStatus::ACTIVE->value,
            'phone' => '08123456789',
        ]);

        $response->assertStatus(201);
    }

    public function test_admin_can_update_user()
    {
        $this->actingAsUser('admin', ['system.user.manage']);

        $user = User::factory()->create();

        $response = $this->putJson("/api/v1/system/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_admin_cannot_delete_self()
    {
        $currentUser = $this->actingAsUser('admin', ['system.user.manage']);

        $response = $this->deleteJson("/api/v1/system/users/{$currentUser->id}");

        $response->assertStatus(422)
            ->assertJson(['message' => 'Tidak dapat menghapus akun sendiri.']);
    }

    public function test_admin_can_delete_other_user()
    {
        $this->actingAsUser('admin', ['system.user.manage']);
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/system/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User berhasil dihapus.']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_admin_can_manage_roles()
    {
        $this->actingAsUser('admin', ['system.role.view', 'system.role.manage']);

        // Create
        $response = $this->postJson('/api/v1/system/roles', [
            'name' => 'test-role',
            'display_name' => 'Test Role Display',
            'description' => 'Test role description',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'test-role');

        $roleId = $response->json('data.id');

        // Update
        $response = $this->putJson("/api/v1/system/roles/{$roleId}", [
            'display_name' => 'Updated Role Display',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.display_name', 'Updated Role Display');

        // Sync permission
        $permission = Permission::firstOrCreate(
            ['name' => 'system.user.view'],
            [
                'module' => 'system',
                'resource' => 'user',
                'action' => 'view',
                'display_name' => 'View User',
            ]
        );

        $response = $this->postJson("/api/v1/system/roles/{$roleId}/permissions", [
            'permission_ids' => [$permission->id],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Permissions role berhasil disinkronkan.']);

        // Delete
        $response = $this->deleteJson("/api/v1/system/roles/{$roleId}");
        $response->assertStatus(200)
            ->assertJson(['message' => 'Role berhasil dihapus.']);
    }
}
