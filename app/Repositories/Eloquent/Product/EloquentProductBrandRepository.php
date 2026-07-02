<?php

namespace App\Repositories\Eloquent\Product;

use App\Models\Product\ProductBrand;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductBrandRepository implements ProductBrandRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ProductBrand::query()
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->where('brand_name', 'like', "%{$s}%")
                    ->orWhere('brand_code', 'like', "%{$s}%")
            )
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?ProductBrand
    {
        return ProductBrand::find($id);
    }

    public function create(array $data): ProductBrand
    {
        return ProductBrand::create($data);
    }

    public function update(ProductBrand $brand, array $data): ProductBrand
    {
        $brand->update($data);

        return $brand->fresh();
    }

    public function delete(ProductBrand $brand): void
    {
        $brand->delete();
    }

    public function listAll(): Collection
    {
        return ProductBrand::latest()->get();
    }

    public function listActive(): Collection
    {
        return ProductBrand::active()->orderBy('brand_name')->get();
    }
}
