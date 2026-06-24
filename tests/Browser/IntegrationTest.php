<?php

use App\Models\System\User;
use App\Models\MasterData\Supplier;
use App\Models\Product\Product;

test('inventory page loads without errors', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/inventory')
        ->assertSuccessful()
        ->assertViewIs('inventory.index')
        ->assertNoJavaScriptErrors();
});

test('pricing page displays price lists', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/pricing')
        ->assertSuccessful()
        ->assertViewIs('pricing.index');
});

test('reporting page generates sales report', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/v1/reports/sales', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
        ])
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'total_sales',
                'total_transactions',
                'average_sale',
            ],
        ]);
});

test('accounting page displays chart of accounts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/accounting')
        ->assertSuccessful();
});

test('purchasing page manages purchase orders', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/v1/purchasing/purchase-orders', [
            'supplier_id' => $supplier->id,
            'po_date' => now()->toDateString(),
            'expected_date' => now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 100,
                    'unit_id' => 1,
                    'unit_cost' => 5000,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'DRAFT');
});
