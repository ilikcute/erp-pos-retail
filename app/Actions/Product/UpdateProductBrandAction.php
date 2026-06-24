<?php

namespace App\Actions\Product;

use App\Models\Product\ProductBrand;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;

class UpdateProductBrandAction
{
    public function __construct(
        private ProductBrandRepositoryInterface $brandRepository
    ) {}

    public function execute(ProductBrand $brand, array $data): ProductBrand
    {
        // Map frontend fields (code, name) to DB fields (brand_code, brand_name)
        $mappedData = [];
        if (isset($data['code'])) {
            $mappedData['brand_code'] = $data['code'];
        }
        if (isset($data['name'])) {
            $mappedData['brand_name'] = $data['name'];
        }
        if (array_key_exists('description', $data)) {
            $mappedData['description'] = $data['description'];
        }
        if (isset($data['is_active'])) {
            $mappedData['is_active'] = $data['is_active'];
        }

        return $this->brandRepository->update($brand, $mappedData);
    }
}
