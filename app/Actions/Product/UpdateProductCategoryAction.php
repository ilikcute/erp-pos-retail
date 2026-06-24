<?php

namespace App\Actions\Product;

use App\Models\Product\ProductCategory;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;

class UpdateProductCategoryAction
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(ProductCategory $category, array $data): ProductCategory
    {
        // Map frontend fields (name) to DB fields (category_name)
        $mappedData = [];
        if (isset($data['name'])) {
            $mappedData['category_name'] = $data['name'];
        }
        if (array_key_exists('description', $data)) {
            $mappedData['description'] = $data['description'];
        }
        if (array_key_exists('parent_id', $data)) {
            $mappedData['parent_id'] = $data['parent_id'];
        }
        if (isset($data['is_active'])) {
            $mappedData['is_active'] = $data['is_active'];
        }

        return $this->categoryRepository->update($category, $mappedData);
    }
}
