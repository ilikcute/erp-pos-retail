<?php

namespace App\Repositories\Contracts\Product;

use App\Models\Product\ProductVariant;

interface ProductVariantRepositoryInterface
{
    public function findById(int $id): ?ProductVariant;

    public function findByBarcode(string $barcode): ?ProductVariant;

    public function create(array $data): ProductVariant;

    public function update(ProductVariant $variant, array $data): ProductVariant;

    public function delete(ProductVariant $variant): void;
}
