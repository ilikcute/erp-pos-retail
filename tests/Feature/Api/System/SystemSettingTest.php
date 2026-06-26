<?php

namespace Tests\Feature\Api\System;

use App\Models\System\AuditLog;
use App\Models\System\BusinessProfile;
use App\Models\System\SystemSetting;
use Tests\ApiTestCase;

class SystemSettingTest extends ApiTestCase
{
    public function test_unauthorized_user_cannot_view_settings()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/system/settings');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_settings()
    {
        $this->actingAsUser('admin', ['system.setting.view']);

        // Seed a setting
        SystemSetting::firstOrCreate(
            ['key' => 'company_name'],
            [
                'value' => 'ERP Retail Ltd',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nama perusahaan',
            ]
        );

        $response = $this->getJson('/api/v1/system/settings');

        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'company_name']);
    }

    public function test_authorized_user_can_update_setting_and_creates_audit_log()
    {
        $this->actingAsUser('admin', ['system.setting.view', 'system.setting.manage']);

        SystemSetting::firstOrCreate(
            ['key' => 'timezone'],
            [
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Zona waktu',
            ]
        );

        $response = $this->putJson('/api/v1/system/settings/timezone', [
            'value' => 'Asia/Jakarta',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Asia/Jakarta', SystemSetting::getValue('timezone'));

        // Assert audit log was written
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'System',
            'action' => 'UPDATE_SETTING',
            'table_name' => 'system_settings',
            'record_id' => 'timezone',
        ]);
    }

    public function test_authorized_user_can_update_bulk_settings()
    {
        $this->actingAsUser('admin', ['system.setting.manage']);

        SystemSetting::firstOrCreate(['key' => 'tax_rate'], ['value' => '10', 'type' => 'integer', 'group' => 'tax']);
        SystemSetting::firstOrCreate(['key' => 'currency_symbol'], ['value' => 'Rp', 'type' => 'string', 'group' => 'general']);

        $response = $this->putJson('/api/v1/system/settings', [
            'settings' => [
                'tax_rate' => '11',
                'currency_symbol' => 'USD',
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(11, SystemSetting::getValue('tax_rate'));
        $this->assertEquals('USD', SystemSetting::getValue('currency_symbol'));
    }

    public function test_user_can_view_business_profile()
    {
        $this->actingAsUser('kasir');

        $profile = BusinessProfile::firstOrNew([]);
        $profile->fill([
            'business_name' => 'My POS Store',
            'legal_name' => 'My POS Store Inc',
        ])->save();

        $response = $this->getJson('/api/v1/system/business-profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.business_name', 'My POS Store');
    }

    public function test_authorized_user_can_update_business_profile()
    {
        $this->actingAsUser('admin', ['system.setting.manage']);

        $response = $this->putJson('/api/v1/system/business-profile', [
            'business_name' => 'Super POS Retail',
            'currency' => 'IDR',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('business_profiles', [
            'business_name' => 'Super POS Retail',
            'currency' => 'IDR',
        ]);
    }

    public function test_authorized_user_can_view_audit_logs()
    {
        $user = $this->actingAsUser('admin', ['system.audit.view']);

        AuditLog::create([
            'user_id' => $user->id,
            'module' => 'System',
            'action' => 'TEST_ACTION',
            'table_name' => 'users',
            'record_id' => 1,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/system/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);

        $logId = $response->json('data.0.id');

        $responseDetail = $this->getJson("/api/v1/system/audit-logs/{$logId}");
        $responseDetail->assertStatus(200)
            ->assertJsonPath('data.action', 'TEST_ACTION');
    }
}
