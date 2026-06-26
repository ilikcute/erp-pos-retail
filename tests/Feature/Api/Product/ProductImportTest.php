<?php

namespace Tests\Feature\Api\Product;

use App\Models\MasterData\Unit;
use App\Models\Product\ProductBrand;
use App\Models\Product\ProductCategory;
use Illuminate\Http\UploadedFile;
use Tests\ApiTestCase;

class ProductImportTest extends ApiTestCase
{
    private Unit $unit;

    private ProductBrand $brand;

    private ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::firstOrCreate(
            ['unit_code' => 'PCS'],
            ['unit_name' => 'Pieces', 'is_active' => true]
        );

        $this->brand = ProductBrand::firstOrCreate(
            ['brand_code' => 'IND'],
            ['brand_name' => 'Indofood', 'is_active' => true]
        );

        $this->category = ProductCategory::firstOrCreate(
            ['category_code' => 'FD'],
            ['category_name' => 'Food', 'is_active' => true]
        );
    }

    public function test_unauthorized_user_cannot_download_import_template(): void
    {
        $this->actingAsUser('kasir', []);

        $this->getJson('/api/v1/product/products/import/template')
            ->assertStatus(403);
    }

    public function test_authorized_user_can_download_import_template(): void
    {
        $this->actingAsUser('admin', ['product.product.create']);

        $response = $this->get('/api/v1/product/products/import/template');

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('application/zip', (string) $response->headers->get('content-type'));
    }

    public function test_user_can_import_products_from_csv(): void
    {
        $this->actingAsUser('admin', ['product.product.create']);

        $csv = implode("\n", [
            'product_code,product_name,product_type,brand_code,category_code,unit_code,description,is_active,is_sellable,is_purchasable,track_stock,min_stock,max_stock,reorder_point,sku,barcode,barcode_type,purchase_price,retail_price',
            'IMP-001,Produk Import Test,SIMPLE,IND,FD,PCS,Test import,1,1,1,1,5,100,10,SKU-IMP-001,8999001001001,EAN13,5000,7500',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->postJson('/api/v1/product/products/import', [
            'file' => $file,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.success', 1)
            ->assertJsonPath('data.failed', 0);

        $this->assertDatabaseHas('products', [
            'product_code' => 'IMP-001',
            'product_name' => 'Produk Import Test',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'SKU-IMP-001',
        ]);

        $this->assertDatabaseHas('product_barcodes', [
            'barcode' => '8999001001001',
        ]);
    }

    public function test_import_returns_validation_errors_for_invalid_row(): void
    {
        $this->actingAsUser('admin', ['product.product.create']);

        $csv = implode("\n", [
            'product_code,product_name,product_type,brand_code,category_code,unit_code,description,is_active,is_sellable,is_purchasable,track_stock,min_stock,max_stock,reorder_point,sku,barcode,barcode_type,purchase_price,retail_price',
            ',Produk Tanpa Kode,SIMPLE,IND,INVALID,PCS,,1,1,1,1,,,,SKU-X,,EAN13,1000,1500',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->postJson('/api/v1/product/products/import', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.failed', 1);
    }
}
