<?php

namespace App\Repositories\Contracts\Product;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
    public function getAll(): Collection;
    public function findByBarcode(string $barcode): ?Product;
}
