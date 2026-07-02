<?php

namespace App\Actions\Product;

use App\Exceptions\BusinessException;
use App\Models\Product\ProductCategory;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;

class DeleteProductCategoryAction
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(ProductCategory $category): bool
    {
        // Validasi: Tidak boleh hapus kategori yang punya children
        if ($category->children()->count() > 0) {
            throw new BusinessException(
                message: 'Cannot delete category with sub-categories',
                errors: ['category' => 'This category has '.$category->children()->count().' sub-category(ies). Please delete them first.']
            );
        }

        // Validasi: Tidak boleh hapus kategori yang digunakan produk
        if ($category->products()->count() > 0) {
            throw new BusinessException(
                message: 'Cannot delete category that is used by products',
                errors: ['category' => 'This category is currently used by '.$category->products()->count().' product(s).']
            );
        }

        return $this->categoryRepository->delete($category);
    }
}
