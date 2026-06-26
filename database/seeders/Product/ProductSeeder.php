<?php

namespace Database\Seeders\Product;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $pcsUnitId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $indofoodBrandId = DB::table('product_brands')->where('brand_code', 'IND')->value('id');
        $cokeBrandId = DB::table('product_brands')->where('brand_code', 'COC')->value('id');
        $foodCatId = DB::table('product_categories')->where('category_code', 'FD')->value('id');
        $bevCatId = DB::table('product_categories')->where('category_code', 'BV')->value('id');
        $pcCatId = DB::table('product_categories')->where('category_code', 'PC')->value('id');

        $retailPriceListId = DB::table('price_lists')->where('price_list_code', 'RETAIL-DEFAULT')->value('id');
        $wholesalePriceListId = DB::table('price_lists')->where('price_list_code', 'WHOLESALE-DEFAULT')->value('id');

        // ═══════════════════════════════════════════════════════════════
        // 1. INDOMIE GORENG (SIMPLE PRODUCT)
        // ═══════════════════════════════════════════════════════════════
        $indomieId = DB::table('products')->insertGetId([
            'product_code' => 'IND-GOR',
            'product_name' => 'Indomie Goreng Spesial',
            'product_type' => 'SIMPLE',
            'brand_id' => $indofoodBrandId,
            'category_id' => $foodCatId,
            'base_unit_id' => $pcsUnitId,
            'description' => 'Mie instan goreng rasa spesial',
            'is_active' => true,
            'is_sellable' => true,
            'is_purchasable' => true,
            'track_stock' => true,
            'min_stock' => 10,
            'max_stock' => 100,
            'reorder_point' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $indomieVariantId = DB::table('product_variants')->insertGetId([
            'product_id' => $indomieId,
            'sku' => 'SKU-IND-GOR',
            'variant_name' => 'Indomie Goreng Spesial',
            'is_default' => true,
            'is_active' => true,
            'purchase_price' => 2500.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_barcodes')->insert([
            'product_variant_id' => $indomieVariantId,
            'barcode' => '8886013850111',
            'barcode_type' => 'EAN13',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_cost_profiles')->insert([
            'product_variant_id' => $indomieVariantId,
            'cost_method' => 'FIFO',
            'standard_cost' => 2500.00,
            'last_cost' => 2500.00,
            'average_cost' => 2500.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_supplier_links')->insert([
            'product_id' => $indomieId,
            'supplier_id' => 1, // Indofood
            'supplier_sku' => 'IND-GOR-RAW',
            'is_preferred' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_account_mappings')->insert([
            'product_id' => $indomieId,
            'inventory_account_id' => 1001,
            'cogs_account_id' => 5001,
            'sales_account_id' => 4001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($retailPriceListId) {
            DB::table('price_list_items')->insert([
                'price_list_id' => $retailPriceListId,
                'product_variant_id' => $indomieVariantId,
                'unit_id' => $pcsUnitId,
                'price' => 3500.00,
                'min_qty' => 1.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($wholesalePriceListId) {
            DB::table('price_list_items')->insert([
                'price_list_id' => $wholesalePriceListId,
                'product_variant_id' => $indomieVariantId,
                'unit_id' => $pcsUnitId,
                'price' => 3000.00,
                'min_qty' => 5.0000, // Minimal 5 untuk grosir
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // 2. COCA COLA 330ML (SIMPLE PRODUCT)
        // ═══════════════════════════════════════════════════════════════
        $cokeId = DB::table('products')->insertGetId([
            'product_code' => 'COKE-330',
            'product_name' => 'Coca Cola Kaleng 330ml',
            'product_type' => 'SIMPLE',
            'brand_id' => $cokeBrandId,
            'category_id' => $bevCatId,
            'base_unit_id' => $pcsUnitId,
            'description' => 'Minuman bersoda Coca Cola kemasan kaleng 330ml',
            'is_active' => true,
            'is_sellable' => true,
            'is_purchasable' => true,
            'track_stock' => true,
            'min_stock' => 12,
            'max_stock' => 120,
            'reorder_point' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cokeVariantId = DB::table('product_variants')->insertGetId([
            'product_id' => $cokeId,
            'sku' => 'SKU-COKE-330',
            'variant_name' => 'Coca Cola Kaleng 330ml',
            'is_default' => true,
            'is_active' => true,
            'purchase_price' => 4000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_barcodes')->insert([
            'product_variant_id' => $cokeVariantId,
            'barcode' => '8886000101011',
            'barcode_type' => 'EAN13',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_cost_profiles')->insert([
            'product_variant_id' => $cokeVariantId,
            'cost_method' => 'FIFO',
            'standard_cost' => 4000.00,
            'last_cost' => 4000.00,
            'average_cost' => 4000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_supplier_links')->insert([
            'product_id' => $cokeId,
            'supplier_id' => 2, // Unilever (sebagai dummy supplier ke-2)
            'supplier_sku' => 'COKE-330-CAN',
            'is_preferred' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_account_mappings')->insert([
            'product_id' => $cokeId,
            'inventory_account_id' => 1002,
            'cogs_account_id' => 5002,
            'sales_account_id' => 4002,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($retailPriceListId) {
            DB::table('price_list_items')->insert([
                'price_list_id' => $retailPriceListId,
                'product_variant_id' => $cokeVariantId,
                'unit_id' => $pcsUnitId,
                'price' => 6000.00,
                'min_qty' => 1.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($wholesalePriceListId) {
            DB::table('price_list_items')->insert([
                'price_list_id' => $wholesalePriceListId,
                'product_variant_id' => $cokeVariantId,
                'unit_id' => $pcsUnitId,
                'price' => 5500.00,
                'min_qty' => 6.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // 3. SABUN MANDI (VARIANT PRODUCT)
        // ═══════════════════════════════════════════════════════════════
        $shampooId = DB::table('products')->insertGetId([
            'product_code' => 'SBN-MND',
            'product_name' => 'Sabun Mandi Wangi',
            'product_type' => 'VARIANT',
            'brand_id' => 2, // Unilever
            'category_id' => $pcCatId, // Personal Care
            'base_unit_id' => $pcsUnitId,
            'description' => 'Sabun mandi keluarga wangi segar',
            'is_active' => true,
            'is_sellable' => true,
            'is_purchasable' => true,
            'track_stock' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Atribut produk
        $wangiAttrId = DB::table('product_attributes')->insertGetId([
            'product_id' => $shampooId,
            'attribute_name' => 'Aroma',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roseValId = DB::table('product_attribute_values')->insertGetId([
            'attribute_id' => $wangiAttrId,
            'value' => 'Mawar (Rose)',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $lemonValId = DB::table('product_attribute_values')->insertGetId([
            'attribute_id' => $wangiAttrId,
            'value' => 'Lemon',
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Variants
        $variants = [
            [
                'sku' => 'SKU-SBN-ROSE',
                'name' => 'Sabun Mandi Mawar',
                'barcode' => '8882000000011',
                'purchase_price' => 3000.00,
                'retail_price' => 5000.00,
                'val_id' => $roseValId,
            ],
            [
                'sku' => 'SKU-SBN-LEMON',
                'name' => 'Sabun Mandi Lemon',
                'barcode' => '8882000000028',
                'purchase_price' => 3100.00,
                'retail_price' => 5200.00,
                'val_id' => $lemonValId,
            ],
        ];

        foreach ($variants as $idx => $v) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $shampooId,
                'sku' => $v['sku'],
                'variant_name' => $v['name'],
                'is_default' => $idx === 0,
                'is_active' => true,
                'purchase_price' => $v['purchase_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_barcodes')->insert([
                'product_variant_id' => $variantId,
                'barcode' => $v['barcode'],
                'barcode_type' => 'EAN13',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_cost_profiles')->insert([
                'product_variant_id' => $variantId,
                'cost_method' => 'FIFO',
                'standard_cost' => $v['purchase_price'],
                'last_cost' => $v['purchase_price'],
                'average_cost' => $v['purchase_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_variant_attributes')->insert([
                'product_variant_id' => $variantId,
                'attribute_id' => $wangiAttrId,
                'attribute_value_id' => $v['val_id'],
            ]);

            if ($retailPriceListId) {
                DB::table('price_list_items')->insert([
                    'price_list_id' => $retailPriceListId,
                    'product_variant_id' => $variantId,
                    'unit_id' => $pcsUnitId,
                    'price' => $v['retail_price'],
                    'min_qty' => 1.0000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('product_account_mappings')->insert([
            'product_id' => $shampooId,
            'inventory_account_id' => 1003,
            'cogs_account_id' => 5003,
            'sales_account_id' => 4003,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Products (Simple & Variant), Barcodes, Costs, Supplier Links, & Price List Items berhasil di-seed.');
    }
}
