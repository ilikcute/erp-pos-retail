<?php

namespace App\Actions\Product;

use App\Exceptions\BusinessException;
use App\Models\Product\ProductBrand;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;

class DeleteProductBrandAction
{
    public function __construct(
        private ProductBrandRepositoryInterface $brandRepository
    ) {}

    public function execute(ProductBrand $brand): void
    {
        if ($brand->products()->exists()) {
            throw new BusinessException(
                message: 'Brand masih digunakan oleh produk.',
                errors: ['delete' => 'Brand masih digunakan oleh produk.']
            );
        }

        $this->brandRepository->delete($brand);
    }
}
