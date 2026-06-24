<?php

namespace App\Repositories\Contracts\Product;

use App\Models\Product\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?ProductCategory;

    public function create(array $data): ProductCategory;

    public function update(ProductCategory $category, array $data): ProductCategory;

    public function delete(ProductCategory $category): void;

    public function listActive(): Collection;

    public function getTree(): Collection;

    public function getFlatList(): Collection;
}
