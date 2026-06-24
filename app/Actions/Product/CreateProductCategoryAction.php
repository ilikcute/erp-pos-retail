<?php

namespace App\Actions\Product;

use App\Models\Product\ProductCategory;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use App\Services\System\CodeGeneratorService;

class CreateProductCategoryAction
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categoryRepository,
        private CodeGeneratorService $codeGenerator
    ) {}

    public function execute(array $data): ProductCategory
    {
        // Auto-generate category_code dengan prefix 'PC'
        $categoryCode = $data['code'] ?? null;
        if (empty($categoryCode)) {
            $categoryCode = $this->codeGenerator->generate(
                'product_categories',
                'category_code',
                'PC',
                5
            );
        }

        // Map frontend fields (name) to DB fields (category_name)
        $mappedData = [
            'category_code' => $categoryCode,
            'category_name' => $data['name'],
            'description'   => $data['description'] ?? null,
            'parent_id'     => $data['parent_id'] ?? null,
            'is_active'     => $data['is_active'] ?? true,
        ];

        return $this->categoryRepository->create($mappedData);
    }
}
