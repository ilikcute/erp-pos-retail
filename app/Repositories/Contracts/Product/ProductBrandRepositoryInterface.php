<?php

namespace App\Repositories\Contracts\Product;

use App\Models\Product\ProductBrand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductBrandRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?ProductBrand;

    public function create(array $data): ProductBrand;

    public function update(ProductBrand $brand, array $data): ProductBrand;

    public function delete(ProductBrand $brand): void;

    public function listActive(): Collection;
}
