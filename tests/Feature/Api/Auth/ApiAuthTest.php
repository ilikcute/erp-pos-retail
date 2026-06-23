<?php

namespace Tests\Feature\Api\Auth;

use App\Models\System\User;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Tests\ApiTestCase;

class ApiAuthTest extends ApiTestCase
{
    public function test_user_can_login_with_correct_credentials()
    {
        $password = 'secret-password-123';
        $user = User::factory()->create([
            'email' => 'login-test@erp.com',
            'password' => Hash::make($password),
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $password,
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
                'message',
            ]);
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422); // Validation error
    }

    public function test_logged_in_user_can_access_me_endpoint()
    {
        $user = $this->actingAsUser('kasir');

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_logout()
    {
        $user = $this->actingAsUser('kasir');

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout berhasil.']);
    }

    public function test_user_can_logout_from_all_devices()
    {
        $user = $this->actingAsUser('kasir');

        $response = $this->postJson('/api/v1/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout dari semua perangkat berhasil.']);
    }

    public function test_user_can_change_password()
    {
        $password = 'old-password-123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
            'status' => UserStatus::ACTIVE,
        ]);

        $role = \App\Models\System\Role::firstOrCreate(['name' => 'kasir'], ['display_name' => 'Kasir', 'is_active' => true]);
        $user->roles()->sync([$role->id]);

        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => $password,
            'new_password' => 'new-secure-password-123',
            'new_password_confirmation' => 'new-secure-password-123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password berhasil diubah.']);

        $this->assertTrue(Hash::check('new-secure-password-123', $user->fresh()->password));
    }
}
