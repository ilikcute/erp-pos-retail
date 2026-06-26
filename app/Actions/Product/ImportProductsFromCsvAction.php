<?php

namespace App\Actions\Product;

use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;
use App\Services\Product\ProductImportService;
use App\Services\Product\ProductService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImportProductsFromCsvAction
{
    public function __construct(
        private readonly ProductImportService $importService,
        private readonly ProductService $productService,
        private readonly PriceListItemRepositoryInterface $priceListItemRepository,
    ) {}

    /**
     * @return array{total: int, success: int, failed: int, errors: array<int, array{row: int|string, message: string}>}
     */
    public function execute(UploadedFile $file, ?int $userId = null): array
    {
        $rows = $this->importService->parseCsvFile($file);
        $maps = $this->importService->referenceMaps();

        $results = [
            'total' => count($rows),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $row) {
            $line = $row['_line'] ?? '?';
            unset($row['_line']);

            $mapped = $this->importService->mapRow($row, $maps);

            if ($mapped['errors'] !== []) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $line,
                    'message' => implode('; ', $mapped['errors']),
                ];

                continue;
            }

            try {
                DB::transaction(function () use ($mapped, $maps, $userId) {
                    $payload = $mapped['data'];
                    $retailPrice = (float) ($payload['retail_price'] ?? 0);
                    unset($payload['retail_price']);

                    $payload['created_by'] = $userId;
                    $payload['updated_by'] = $userId;

                    $product = $this->productService->create($payload);

                    if ($retailPrice > 0 && $maps['retail_price_list_id']) {
                        $variant = $product->variants()->where('is_default', true)->first()
                            ?? $product->variants()->first();

                        if ($variant) {
                            $this->priceListItemRepository->createOrUpdate(
                                [
                                    'price_list_id' => $maps['retail_price_list_id'],
                                    'product_variant_id' => $variant->id,
                                    'unit_id' => $product->base_unit_id,
                                    'min_qty' => 1,
                                ],
                                [
                                    'price' => $retailPrice,
                                    'created_by' => $userId,
                                    'updated_by' => $userId,
                                ]
                            );
                        }
                    }
                });

                $results['success']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $line,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
