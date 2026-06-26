<?php

namespace Database\Seeders\Performance;

use Database\Seeders\Performance\Concerns\SeedsPerformanceData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkProductSeeder extends Seeder
{
    use SeedsPerformanceData;

    /** @var array<int, string> */
    private array $productNames = [
        'Beras Premium 5kg', 'Minyak Goreng 2L', 'Gula Pasir 1kg', 'Teh Celup 25s', 'Kopi Bubuk 200g',
        'Susu UHT 1L', 'Air Mineral 600ml', 'Keripik Kentang', 'Biskuit Coklat', 'Mie Instan Ayam',
        'Sabun Cuci Piring', 'Deterjen Bubuk 800g', 'Tissue 250 sheet', 'Shampoo 170ml', 'Pasta Gigi 150g',
        'Tissue Basah', 'Hand Sanitizer 100ml', 'Pembalut Wanita', 'Popok Bayi M', 'Tissue Magic',
        'Air Freshener', 'Lilin Anti Nyamuk', 'Korek Api Gas', 'Pemutih Pakaian', 'Pel Pel Lantai',
        'Saus Sambal 250ml', 'Keju Slice 200g', 'Selai Strawberry', 'Madu Asli 250g', 'Oatmeal 500g',
    ];

    public function run(): void
    {
        $count = $this->performanceCount();

        $pcsUnitId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $brandIds = DB::table('product_brands')->pluck('id')->all();
        $categoryIds = DB::table('product_categories')->pluck('id')->all();
        $retailPriceListId = DB::table('price_lists')->where('price_list_code', 'RETAIL-DEFAULT')->value('id');

        if (! $pcsUnitId || $brandIds === [] || $categoryIds === []) {
            $this->command->warn('⚠️  Units/brands/categories belum ada. Jalankan DatabaseSeeder terlebih dahulu.');

            return;
        }

        $now = now();
        $productRows = [];

        for ($i = 1; $i <= $count; $i++) {
            $seq = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $baseName = $this->productNames[($i - 1) % count($this->productNames)];

            $productRows[] = [
                'product_code' => "PERF-PROD-{$seq}",
                'product_name' => "{$baseName} Perf #{$i}",
                'product_type' => 'SIMPLE',
                'brand_id' => $brandIds[($i - 1) % count($brandIds)],
                'category_id' => $categoryIds[($i - 1) % count($categoryIds)],
                'base_unit_id' => $pcsUnitId,
                'description' => "Produk dummy performance test #{$i}",
                'is_active' => $i % 15 !== 0,
                'is_sellable' => true,
                'is_purchasable' => true,
                'track_stock' => true,
                'min_stock' => 5,
                'max_stock' => 500,
                'reorder_point' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertInChunks('products', $productRows);

        /** @var Collection<int, object{id: int, product_code: string, product_name: string}> $products */
        $products = DB::table('products')
            ->where('product_code', 'like', 'PERF-PROD-%')
            ->orderBy('id')
            ->get(['id', 'product_code', 'product_name']);

        $variantRows = [];
        $variantMeta = [];

        foreach ($products as $index => $product) {
            $seq = str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $purchasePrice = 5_000 + (($index + 1) % 50) * 1_000;

            $variantMeta[] = [
                'product_id' => $product->id,
                'sku' => "SKU-PERF-{$seq}",
                'purchase_price' => $purchasePrice,
                'retail_price' => (int) ($purchasePrice * 1.35),
            ];

            $variantRows[] = [
                'product_id' => $product->id,
                'sku' => "SKU-PERF-{$seq}",
                'variant_name' => $product->product_name,
                'is_default' => true,
                'is_active' => true,
                'purchase_price' => $purchasePrice,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertInChunks('product_variants', $variantRows);

        $variantsBySku = DB::table('product_variants')
            ->where('sku', 'like', 'SKU-PERF-%')
            ->pluck('id', 'sku');

        $barcodeRows = [];
        $priceRows = [];

        foreach ($variantMeta as $index => $meta) {
            $seq = str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $variantId = $variantsBySku[$meta['sku']] ?? null;

            if (! $variantId) {
                continue;
            }

            $barcodeRows[] = [
                'product_variant_id' => $variantId,
                'barcode' => '8992000'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                'barcode_type' => 'EAN13',
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($retailPriceListId) {
                $priceRows[] = [
                    'price_list_id' => $retailPriceListId,
                    'product_variant_id' => $variantId,
                    'unit_id' => $pcsUnitId,
                    'price' => $meta['retail_price'],
                    'min_qty' => 1.0000,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->insertInChunks('product_barcodes', $barcodeRows);

        if ($priceRows !== []) {
            $this->insertInChunks('price_list_items', $priceRows);
        }

        $this->command->info("✅ {$count} bulk products + variants + barcodes + price items berhasil di-seed.");
    }
}
