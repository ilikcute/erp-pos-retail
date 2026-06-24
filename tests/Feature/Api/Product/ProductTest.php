<?php

namespace Tests\Feature\Api\Product;

use App\Models\MasterData\Unit;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\ProductBarcode;
use Tests\ApiTestCase;

class ProductTest extends ApiTestCase
{
    private Unit $unit;

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
    }

    public function test_unauthorized_user_cannot_view_products()
    {
        $this->actingAsUser('kasir', []);

        $response = $this->getJson('/api/v1/product/products');

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_products()
    {
        $this->actingAsUser('kasir', ['product.product.view']);

        $product = Product::create([
            'product_code' => 'PROD-A',
            'product_name' => 'Product Alpha',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $response = $this->getJson('/api/v1/product/products');

        $response->assertStatus(200)
            ->assertJsonFragment(['product_code' => 'PROD-A']);
    }

    public function test_user_can_create_simple_product()
    {
        $this->actingAsUser('admin', ['product.product.create']);

        $response = $this->postJson('/api/v1/product/products', [
            'product_code' => 'PROD-B',
            'product_name' => 'Product Beta',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
            'is_purchasable' => true,
            'track_stock' => true,
            'default_variant' => [
                'sku' => 'SKU-PROD-B',
                'barcode' => '8881234567890',
                'barcode_type' => 'EAN13',
                'weight' => 0.5,
                'purchase_price' => 10000,
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'product_code' => 'PROD-B',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'sku' => 'SKU-PROD-B',
        ]);
        $this->assertDatabaseHas('product_barcodes', [
            'barcode' => '8881234567890',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'purchase_price' => 10000,
        ]);
    }

    public function test_user_can_create_variant_product_with_attributes()
    {
        $this->actingAsUser('admin', ['product.product.create']);

        $response = $this->postJson('/api/v1/product/products', [
            'product_code' => 'PROD-V',
            'product_name' => 'Product Variant Test',
            'product_type' => 'VARIANT',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
            'is_purchasable' => true,
            'track_stock' => true,
            'attributes' => [
                [
                    'attribute_name' => 'Warna',
                    'values' => ['Merah', 'Biru'],
                ]
            ],
            'variants' => [
                [
                    'sku' => 'SKU-PROD-V-MERAH',
                    'variant_name' => 'Product Variant Test - Merah',
                    'barcode' => '1234567890123',
                    'barcode_type' => 'EAN13',
                    'weight' => 0.2,
                    'purchase_price' => 20000,
                    'attribute_values' => [
                        [
                            'attribute_name' => 'Warna',
                            'value' => 'Merah'
                        ]
                    ]
                ],
                [
                    'sku' => 'SKU-PROD-V-BIRU',
                    'variant_name' => 'Product Variant Test - Biru',
                    'barcode' => '1234567890124',
                    'barcode_type' => 'EAN13',
                    'weight' => 0.2,
                    'purchase_price' => 20000,
                    'attribute_values' => [
                        [
                            'attribute_name' => 'Warna',
                            'value' => 'Biru'
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('products', [
            'product_code' => 'PROD-V',
            'product_type' => 'VARIANT'
        ]);

        $this->assertDatabaseHas('product_attributes', [
            'attribute_name' => 'Warna'
        ]);

        $this->assertDatabaseHas('product_attribute_values', [
            'value' => 'Merah'
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'SKU-PROD-V-MERAH',
            'variant_name' => 'Product Variant Test - Merah'
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'SKU-PROD-V-BIRU',
            'variant_name' => 'Product Variant Test - Biru'
        ]);
    }

    public function test_user_can_update_product()
    {
        $this->actingAsUser('admin', ['product.product.update']);

        $product = Product::create([
            'product_code' => 'PROD-C',
            'product_name' => 'Product Gamma',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $response = $this->putJson("/api/v1/product/products/{$product->id}", [
            'product_name' => 'Product Gamma Updated',
            'base_unit_id' => $this->unit->id,
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Product Gamma Updated', $product->fresh()->product_name);
        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_user_can_find_product_by_barcode()
    {
        $this->actingAsUser('kasir', ['product.product.view']);

        $product = Product::create([
            'product_code' => 'PROD-D',
            'product_name' => 'Product Delta',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-PROD-D',
            'variant_name' => 'Default',
            'is_default' => true,
            'is_active' => true,
        ]);

        ProductBarcode::create([
            'product_variant_id' => $variant->id,
            'barcode' => '9991234567890',
            'barcode_type' => 'EAN13',
            'is_primary' => true,
        ]);

        $response = $this->getJson('/api/v1/product/products/barcode/9991234567890');

        $response->assertStatus(200)
            ->assertJsonPath('data.sku', 'SKU-PROD-D');
    }

    public function test_user_can_add_variant_to_product()
    {
        $this->actingAsUser('admin', ['product.product.update']);

        $product = Product::create([
            'product_code' => 'PROD-E',
            'product_name' => 'Product Epsilon',
            'product_type' => 'VARIANT',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $response = $this->postJson("/api/v1/product/products/{$product->id}/variants", [
            'sku' => 'SKU-PROD-E-RED',
            'variant_name' => 'Red Option',
            'barcode' => '7771234567890',
            'weight' => 0.6,
            'purchase_price' => 15000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'SKU-PROD-E-RED',
        ]);
        $this->assertDatabaseHas('product_barcodes', [
            'barcode' => '7771234567890',
        ]);
    }

    public function test_user_can_delete_product()
    {
        $this->actingAsUser('admin', ['product.product.delete']);

        $product = Product::create([
            'product_code' => 'PROD-F',
            'product_name' => 'Product Zeta',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $response = $this->deleteJson("/api/v1/product/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
