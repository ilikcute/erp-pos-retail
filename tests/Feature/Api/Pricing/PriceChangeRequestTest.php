<?php

namespace Tests\Feature\Api\Pricing;

use App\Models\MasterData\Unit;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Pricing\PriceList;
use App\Models\Pricing\PriceListItem;
use App\Models\Pricing\PriceChangeRequest;
use App\Models\System\DocumentType;
use App\Enums\PriceChangeRequestStatus;
use App\Services\Pricing\PriceChangeRequestService;
use Tests\ApiTestCase;

class PriceChangeRequestTest extends ApiTestCase
{
    private Unit $unit;
    private ProductVariant $variant;
    private PriceList $priceList;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed document type
        DocumentType::firstOrCreate(
            ['code' => 'PRICE_CHANGE_REQUEST'],
            [
                'name' => 'Price Change Request',
                'prefix' => 'PCR',
                'date_format' => 'Ymd',
                'padding' => 4,
                'separator' => '-',
                'is_active' => true,
            ]
        );

        $this->unit = Unit::firstOrCreate(
            ['unit_code' => 'PCS'],
            [
                'unit_name' => 'Pieces',
                'is_active' => true,
            ]
        );

        // 3. Seed product and variant
        $product = Product::create([
            'product_code' => 'PROD-Y',
            'product_name' => 'Product Y',
            'product_type' => 'SIMPLE',
            'base_unit_id' => $this->unit->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-Y',
            'variant_name' => 'Default',
            'is_default' => true,
            'is_active' => true,
            'purchase_price' => 2000,
        ]);

        // 4. Seed price list and item
        $this->priceList = PriceList::create([
            'price_list_code' => 'PL-Y',
            'price_list_name' => 'Price List Y',
            'price_list_type' => 'RETAIL',
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $this->priceList->id,
            'product_variant_id' => $this->variant->id,
            'unit_id' => $this->unit->id,
            'price' => 3000.00,
            'min_qty' => 1.0,
        ]);
    }

    public function test_user_can_create_price_change_request()
    {
        $this->actingAsUser('admin', ['pricing.price-change-request.create']);

        $response = $this->postJson('/api/v1/pricing/price-change-requests', [
            'price_list_id' => $this->priceList->id,
            'effective_date' => now()->addDay()->toDateString(),
            'reason' => 'Harga modal naik',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'unit_id' => $this->unit->id,
                    'old_price' => 3000,
                    'new_price' => 3500,
                    'change_reason' => 'Penyesuaian supplier',
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'DRAFT');

        $this->assertDatabaseHas('price_change_requests', [
            'price_list_id' => $this->priceList->id,
            'reason' => 'Harga modal naik',
        ]);
    }

    public function test_authorized_user_can_reject_price_change_request()
    {
        $approver = $this->actingAsUser('manager', ['pricing.price-change-request.approve']);

        // Create PCR and set status to PENDING programmatically
        $service = app(PriceChangeRequestService::class);
        $pcr = $service->create([
            'price_list_id' => $this->priceList->id,
            'effective_date' => now()->addDay()->toDateString(),
            'reason' => 'Harga modal naik',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'unit_id' => $this->unit->id,
                    'old_price' => 3000,
                    'new_price' => 3500,
                ]
            ]
        ]);
        $pcr = $service->submit($pcr);

        $response = $this->postJson("/api/v1/pricing/price-change-requests/{$pcr->id}/reject", [
            'reason' => 'Harga baru terlalu mahal',
        ]);

        $response->assertStatus(200);

        $freshPcr = $pcr->fresh();
        // Assert that status returns to DRAFT
        $this->assertEquals(PriceChangeRequestStatus::DRAFT, $freshPcr->status);
        $this->assertEquals($approver->id, $freshPcr->rejected_by);
        $this->assertEquals('Harga baru terlalu mahal', $freshPcr->rejection_reason);
    }

    public function test_authorized_user_can_approve_price_change_request()
    {
        $approver = $this->actingAsUser('manager', ['pricing.price-change-request.approve']);

        $service = app(PriceChangeRequestService::class);
        $pcr = $service->create([
            'price_list_id' => $this->priceList->id,
            'effective_date' => now()->addDay()->toDateString(),
            'reason' => 'Harga modal naik',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'unit_id' => $this->unit->id,
                    'old_price' => 3000,
                    'new_price' => 3500,
                ]
            ]
        ]);
        $pcr = $service->submit($pcr);

        $response = $this->postJson("/api/v1/pricing/price-change-requests/{$pcr->id}/approve");

        $response->assertStatus(200);

        $freshPcr = $pcr->fresh();
        $this->assertEquals(PriceChangeRequestStatus::APPROVED, $freshPcr->status);
        $this->assertEquals($approver->id, $freshPcr->approved_by);
    }

    public function test_authorized_user_can_apply_price_change_request()
    {
        $user = $this->actingAsUser('manager', [
            'pricing.price-change-request.approve',
            'pricing.price-change-request.apply'
        ]);

        $service = app(PriceChangeRequestService::class);
        $pcr = $service->create([
            'price_list_id' => $this->priceList->id,
            'effective_date' => now()->addDay()->toDateString(),
            'reason' => 'Harga modal naik',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'unit_id' => $this->unit->id,
                    'old_price' => 3000,
                    'new_price' => 3500,
                ]
            ]
        ]);
        $pcr = $service->submit($pcr);
        $pcr = $service->approve($pcr, $user->id);

        $response = $this->postJson("/api/v1/pricing/price-change-requests/{$pcr->id}/apply");

        $response->assertStatus(200);

        $freshPcr = $pcr->fresh();
        $this->assertEquals(PriceChangeRequestStatus::APPLIED, $freshPcr->status);

        // Verify that the price in the Price List Item is updated to 3500
        $this->assertDatabaseHas('price_list_items', [
            'price_list_id' => $this->priceList->id,
            'product_variant_id' => $this->variant->id,
            'price' => 3500.00,
        ]);

        // Verify that price history is recorded
        $this->assertDatabaseHas('price_histories', [
            'price_list_id' => $this->priceList->id,
            'product_variant_id' => $this->variant->id,
            'old_price' => 3000.00,
            'new_price' => 3500.00,
            'price_change_request_id' => $pcr->id,
        ]);
    }
}
