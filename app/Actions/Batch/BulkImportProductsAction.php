<?php

namespace App\Actions\Batch;

use App\Models\Product\Product;
use App\Models\MasterData\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkImportProductsAction
{
    public function execute(array $products): array
    {
        $results = [
            'total' => count($products),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        return DB::transaction(function () use ($products, $results) {
            foreach ($products as $index => $productData) {
                try {
                    $this->validateProductData($productData);

                    $product = Product::create([
                        'product_code'      => $productData['product_code'],
                        'product_name'      => $productData['product_name'],
                        'product_type'      => $productData['product_type'] ?? 'SIMPLE',
                        'description'       => $productData['description'] ?? null,
                        'product_category_id' => $productData['category_id'],
                        'product_brand_id'  => $productData['brand_id'] ?? null,
                        'unit_id'           => $productData['unit_id'],
                        'is_active'         => $productData['is_active'] ?? true,
                        'created_by'        => auth()->id(),
                    ]);

                    if (!empty($productData['variants'])) {
                        foreach ($productData['variants'] as $variant) {
                            $product->variants()->create([
                                'variant_code'     => $variant['variant_code'],
                                'variant_name'     => $variant['variant_name'],
                                'sku'              => $variant['sku'],
                                'created_by'       => auth()->id(),
                            ]);
                        }
                    }

                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'message' => $e->getMessage(),
                    ];
                    Log::error("Bulk import error at row {$index}: {$e->getMessage()}");
                }
            }

            return $results;
        });
    }

    private function validateProductData(array $data): void
    {
        if (empty($data['product_code'])) {
            throw new \InvalidArgumentException('Product code is required');
        }
        if (empty($data['product_name'])) {
            throw new \InvalidArgumentException('Product name is required');
        }
        if (empty($data['category_id'])) {
            throw new \InvalidArgumentException('Category ID is required');
        }
        if (empty($data['unit_id'])) {
            throw new \InvalidArgumentException('Unit ID is required');
        }
    }
}
