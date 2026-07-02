<?php

namespace Tests\Feature\Api\MasterData;

use App\Models\MasterData\Tax;
use App\Models\MasterData\Unit;
use Tests\ApiTestCase;

class UnitAndTaxTest extends ApiTestCase
{
    // ─── Unit Tests ───────────────────────────────────────────────────

    public function test_unauthorized_user_cannot_view_units()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/master-data/units');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_units()
    {
        $this->actingAsUser('kasir', ['master-data.unit.view']);

        Unit::create([
            'unit_code' => 'PCS-TEST',
            'unit_name' => 'Pieces Test',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/master-data/units');

        $response->assertStatus(200)
            ->assertJsonFragment(['unit_code' => 'PCS-TEST']);
    }

    public function test_user_can_create_unit()
    {
        $this->actingAsUser('admin', ['master-data.unit.manage']);

        $response = $this->postJson('/api/v1/master-data/units', [
            'unit_code' => 'BOX-TEST',
            'unit_name' => 'Box Test',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('units', [
            'unit_code' => 'BOX-TEST',
        ]);
    }

    public function test_user_can_update_unit()
    {
        $this->actingAsUser('admin', ['master-data.unit.manage']);

        $unit = Unit::create([
            'unit_code' => 'KNG-TEST',
            'unit_name' => 'Kaleng Test',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/master-data/units/{$unit->id}", [
            'unit_name' => 'Kaleng Update',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Kaleng Update', $unit->fresh()->unit_name);
        $this->assertFalse($unit->fresh()->is_active);
    }

    public function test_user_can_delete_unit()
    {
        $this->actingAsUser('admin', ['master-data.unit.manage']);

        $unit = Unit::create([
            'unit_code' => 'PKT-TEST',
            'unit_name' => 'Paket Test',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/master-data/units/{$unit->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    // ─── Tax Tests ────────────────────────────────────────────────────

    public function test_unauthorized_user_cannot_view_taxes()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/master-data/taxes');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_taxes()
    {
        $this->actingAsUser('kasir', ['master-data.tax.view']);

        Tax::create([
            'tax_code' => 'PPN-11',
            'tax_name' => 'PPN 11%',
            'tax_rate' => 11.00,
            'is_inclusive' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/master-data/taxes');

        $response->assertStatus(200)
            ->assertJsonFragment(['tax_code' => 'PPN-11']);
    }

    public function test_user_can_create_tax()
    {
        $this->actingAsUser('admin', ['master-data.tax.manage']);

        $response = $this->postJson('/api/v1/master-data/taxes', [
            'tax_code' => 'PPN-12',
            'tax_name' => 'PPN 12%',
            'tax_rate' => 12.00,
            'is_inclusive' => false,
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('taxes', [
            'tax_code' => 'PPN-12',
        ]);
    }

    public function test_user_can_update_tax()
    {
        $this->actingAsUser('admin', ['master-data.tax.manage']);

        $tax = Tax::create([
            'tax_code' => 'TAX-UP',
            'tax_name' => 'Tax Update',
            'tax_rate' => 5.00,
            'is_inclusive' => false,
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/master-data/taxes/{$tax->id}", [
            'tax_name' => 'Tax Update New',
            'tax_rate' => 6.00,
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Tax Update New', $tax->fresh()->tax_name);
        $this->assertEquals(6.00, $tax->fresh()->tax_rate);
        $this->assertFalse($tax->fresh()->is_active);
    }

    public function test_user_can_delete_tax()
    {
        $this->actingAsUser('admin', ['master-data.tax.manage']);

        $tax = Tax::create([
            'tax_code' => 'TAX-DEL',
            'tax_name' => 'Tax Delete',
            'tax_rate' => 2.50,
            'is_inclusive' => false,
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/master-data/taxes/{$tax->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('taxes', ['id' => $tax->id]);
    }
}
