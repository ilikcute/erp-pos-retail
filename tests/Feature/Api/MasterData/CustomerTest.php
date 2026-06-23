<?php

namespace Tests\Feature\Api\MasterData;

use App\Models\MasterData\Customer;
use App\Models\MasterData\CustomerCategory;
use Tests\ApiTestCase;

class CustomerTest extends ApiTestCase
{
    public function test_unauthorized_user_cannot_view_customers()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/master-data/customers');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_customers()
    {
        $this->actingAsUser('kasir', ['master-data.customer.view']);

        $category = CustomerCategory::create([
            'category_code' => 'GOLD',
            'category_name' => 'Gold Member',
            'is_active' => true,
        ]);

        Customer::create([
            'customer_code' => 'CUST-001',
            'customer_name' => 'Customer John',
            'customer_category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/master-data/customers');

        $response->assertStatus(200)
            ->assertJsonFragment(['customer_code' => 'CUST-001']);
    }

    public function test_user_can_create_customer()
    {
        $this->actingAsUser('admin', ['master-data.customer.create']);

        $category = CustomerCategory::create([
            'category_code' => 'SILVER',
            'category_name' => 'Silver Member',
        ]);

        $response = $this->postJson('/api/v1/master-data/customers', [
            'customer_code' => 'CUST-TEST-01',
            'customer_name' => 'Jane Doe',
            'customer_category_id' => $category->id,
            'email' => 'jane@doe.com',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('customers', [
            'customer_code' => 'CUST-TEST-01',
        ]);
    }

    public function test_user_can_update_customer()
    {
        $this->actingAsUser('admin', ['master-data.customer.update']);

        $category = CustomerCategory::create([
            'category_code' => 'BRONZE',
            'category_name' => 'Bronze Member',
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUST-UP-01',
            'customer_name' => 'Old Name',
            'customer_category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/master-data/customers/{$customer->id}", [
            'customer_name' => 'New Name Updated',
            'customer_category_id' => $category->id,
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Name Updated', $customer->fresh()->customer_name);
        $this->assertFalse($customer->fresh()->is_active);
    }

    public function test_user_can_delete_customer()
    {
        $this->actingAsUser('admin', ['master-data.customer.delete']);

        $category = CustomerCategory::create([
            'category_code' => 'PLATINUM',
            'category_name' => 'Platinum Member',
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUST-DEL-01',
            'customer_name' => 'Delete Me',
            'customer_category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/master-data/customers/{$customer->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_customer_category_lifecycle()
    {
        $this->actingAsUser('admin', [
            'master-data.customer.view',
            'master-data.customer.create',
            'master-data.customer.update',
            'master-data.customer.delete',
        ]);

        // Create Category
        $response = $this->postJson('/api/v1/master-data/customer-categories', [
            'category_code' => 'CAT-TEST',
            'category_name' => 'Test Category',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $categoryId = $response->json('data.id');

        // Update Category
        $response = $this->putJson("/api/v1/master-data/customer-categories/{$categoryId}", [
            'category_name' => 'Test Category Updated',
        ]);
        $response->assertStatus(200);

        // Deleting when used by Customer should fail
        Customer::create([
            'customer_code' => 'CUST-LIFECYCLE',
            'customer_name' => 'Lifecycle Cust',
            'customer_category_id' => $categoryId,
        ]);

        $responseDelete = $this->deleteJson("/api/v1/master-data/customer-categories/{$categoryId}");
        $responseDelete->assertStatus(422)
            ->assertJson(['message' => 'Kategori masih digunakan oleh customer.']);

        // Delete customer first, then delete category should succeed
        Customer::where('customer_category_id', $categoryId)->delete();

        $responseDeleteSucceed = $this->deleteJson("/api/v1/master-data/customer-categories/{$categoryId}");
        $responseDeleteSucceed->assertStatus(200);
    }
}
