<?php

namespace App\Actions\Product;

use App\Models\Product\ProductBrand;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;

class CreateProductBrandAction
{
    public function __construct(
        private ProductBrandRepositoryInterface $brandRepository
    ) {}

    public function execute(array $data): ProductBrand
    {
        // Map frontend fields (code, name) to DB fields (brand_code, brand_name)
        $mappedData = [
            'brand_code' => $data['code'],
            'brand_name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        return $this->brandRepository->create($mappedData);
    }
}
