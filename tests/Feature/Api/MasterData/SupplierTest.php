<?php

namespace Tests\Feature\Api\MasterData;

use App\Models\MasterData\Supplier;
use Tests\ApiTestCase;

class SupplierTest extends ApiTestCase
{
    public function test_unauthorized_user_cannot_view_suppliers()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/master-data/suppliers');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_suppliers()
    {
        $this->actingAsUser('kasir', ['master-data.supplier.view']);

        Supplier::create([
            'supplier_code' => 'SUP-001',
            'supplier_name' => 'Supplier A',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/master-data/suppliers');

        $response->assertStatus(200)
            ->assertJsonFragment(['supplier_code' => 'SUP-001']);
    }

    public function test_user_can_create_supplier_and_creates_audit_log()
    {
        $this->actingAsUser('admin', ['master-data.supplier.create']);

        $response = $this->postJson('/api/v1/master-data/suppliers', [
            'supplier_code' => 'SUP-TEST-02',
            'supplier_name' => 'Supplier Test 2',
            'email' => 'supplier2@test.com',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('suppliers', [
            'supplier_code' => 'SUP-TEST-02',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'MasterData',
            'action' => 'CREATE_SUPPLIER',
            'table_name' => 'suppliers',
        ]);
    }

    public function test_user_can_update_supplier_and_creates_audit_log()
    {
        $this->actingAsUser('admin', ['master-data.supplier.update']);

        $supplier = Supplier::create([
            'supplier_code' => 'SUP-UPDATE-01',
            'supplier_name' => 'Supplier Update 1',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/master-data/suppliers/{$supplier->id}", [
            'supplier_name' => 'Supplier Brand New Name',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Supplier Brand New Name', $supplier->fresh()->supplier_name);
        $this->assertFalse($supplier->fresh()->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'MasterData',
            'action' => 'UPDATE_SUPPLIER',
            'table_name' => 'suppliers',
            'record_id' => $supplier->id,
        ]);
    }

    public function test_user_can_delete_supplier_and_creates_audit_log()
    {
        $this->actingAsUser('admin', ['master-data.supplier.delete']);

        $supplier = Supplier::create([
            'supplier_code' => 'SUP-DEL-01',
            'supplier_name' => 'Supplier Delete 1',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/master-data/suppliers/{$supplier->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'MasterData',
            'action' => 'DELETE_SUPPLIER',
            'table_name' => 'suppliers',
            'record_id' => $supplier->id,
        ]);
    }
}
