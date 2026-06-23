<?php

namespace Tests\Feature\Api\Pricing;

use App\Models\MasterData\Unit;
use App\Models\MasterData\Customer;
use App\Models\MasterData\CustomerCategory;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Pricing\PriceList;
use App\Models\Pricing\PriceListItem;
use Tests\ApiTestCase;

class PriceListTest extends ApiTestCase
{
    private Unit $unit;
    private ProductVariant $variant;
    private CustomerCategory $category;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::firstOrCreate(
            ['unit_code' => 'PCS'],
            [
                'unit_name' => 'Pieces',
                'is_active' => true,
            ]
        );

        $product = Product::create([
            'product_code' => 'PROD-X',
            'product_name' => 'Product X',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-X',
            'variant_name' => 'Default',
            'is_default' => true,
            'is_active' => true,
            'purchase_price' => 1000,
        ]);

        $this->category = CustomerCategory::create([
            'category_code' => 'WHOLESALE-CAT',
            'category_name' => 'Wholesale Member',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-X',
            'customer_name' => 'John Wholesaler',
            'customer_category_id' => $this->category->id,
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_view_price_lists()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/pricing/price-lists');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_price_lists()
    {
        $this->actingAsUser('kasir', ['pricing.price-list.view']);

        PriceList::create([
            'price_list_code' => 'RET-01',
            'price_list_name' => 'Retail Price List',
            'price_list_type' => 'RETAIL',
            'is_active' => true,
            'is_default' => true,
        ]);

        $response = $this->getJson('/api/v1/pricing/price-lists');

        $response->assertStatus(200)
            ->assertJsonFragment(['price_list_code' => 'RET-01']);
    }

    public function test_user_can_create_price_list()
    {
        $this->actingAsUser('admin', ['pricing.price-list.manage']);

        $response = $this->postJson('/api/v1/pricing/price-lists', [
            'price_list_code' => 'WHO-01',
            'price_list_name' => 'Wholesale Price List',
            'price_list_type' => 'WHOLESALE',
            'is_default' => false,
            'is_active' => true,
            'customer_category_ids' => [$this->category->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('price_lists', [
            'price_list_code' => 'WHO-01',
        ]);
    }

    public function test_user_can_update_price_list()
    {
        $this->actingAsUser('admin', ['pricing.price-list.manage']);

        $priceList = PriceList::create([
            'price_list_code' => 'UPD-01',
            'price_list_name' => 'Old Name',
            'price_list_type' => 'SPECIAL',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/pricing/price-lists/{$priceList->id}", [
            'price_list_name' => 'New Name Price List',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Name Price List', $priceList->fresh()->price_list_name);
        $this->assertFalse($priceList->fresh()->is_active);
    }

    public function test_user_can_store_and_list_price_list_items()
    {
        $this->actingAsUser('admin', [
            'pricing.price-list.view',
            'pricing.price-list.manage',
        ]);

        $priceList = PriceList::create([
            'price_list_code' => 'ITEMS-01',
            'price_list_name' => 'Items List',
            'price_list_type' => 'RETAIL',
            'is_active' => true,
        ]);

        // Add item
        $response = $this->postJson("/api/v1/pricing/price-lists/{$priceList->id}/items", [
            'product_variant_id' => $this->variant->id,
            'unit_id' => $this->unit->id,
            'price' => 5000,
            'min_qty' => 1.0,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('price_list_items', [
            'price_list_id' => $priceList->id,
            'price' => 5000,
        ]);

        // List items
        $responseList = $this->getJson("/api/v1/pricing/price-lists/{$priceList->id}/items");
        $responseList->assertStatus(200)
            ->assertJsonFragment(['price' => "5000.00"]);
    }

    public function test_price_resolver_logic()
    {
        $this->actingAsUser('kasir');

        // 1. Fetch or Create Default Retail Price List (Price: 5000)
        $defaultList = PriceList::where('is_default', true)
            ->where('price_list_type', \App\Enums\PriceListType::RETAIL->value)
            ->active()
            ->first();

        if (! $defaultList) {
            $defaultList = PriceList::create([
                'price_list_code' => 'DEF-RET',
                'price_list_name' => 'Default Retail',
                'price_list_type' => 'RETAIL',
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        PriceListItem::create([
            'price_list_id' => $defaultList->id,
            'product_variant_id' => $this->variant->id,
            'unit_id' => $this->unit->id,
            'price' => 5000.00,
            'min_qty' => 1.0,
        ]);

        // 2. Create Wholesale Price List (Price: 4000) mapped to customer category
        $wholesaleList = PriceList::create([
            'price_list_code' => 'WHO-CAT',
            'price_list_name' => 'Wholesale Member',
            'price_list_type' => 'WHOLESALE',
            'is_default' => false,
            'is_active' => true,
        ]);

        $wholesaleList->customerCategories()->sync([$this->category->id]);

        PriceListItem::create([
            'price_list_id' => $wholesaleList->id,
            'product_variant_id' => $this->variant->id,
            'unit_id' => $this->unit->id,
            'price' => 4000.00,
            'min_qty' => 1.0,
        ]);

        // Scenario A: Resolve price for general walk-in customer (no customer_id) -> Should resolve to 5000
        $responseA = $this->postJson('/api/v1/pricing/price-lists/resolve', [
            'product_variant_id' => $this->variant->id,
            'unit_id' => $this->unit->id,
            'qty' => 1,
        ]);

        $responseA->assertStatus(200);
        // Note: price resolver might return numeric or structure depending on service implementation
        // Let's assert that the returned resolved price value matches 5000
        $this->assertEquals(5000, (float)$responseA->json('data.price'));

        // Scenario B: Resolve price for Wholesale customer -> Should resolve to 4000
        $responseB = $this->postJson('/api/v1/pricing/price-lists/resolve', [
            'product_variant_id' => $this->variant->id,
            'customer_id' => $this->customer->id,
            'unit_id' => $this->unit->id,
            'qty' => 1,
        ]);

        $responseB->assertStatus(200);
        $this->assertEquals(4000, (float)$responseB->json('data.price'));
    }
}
