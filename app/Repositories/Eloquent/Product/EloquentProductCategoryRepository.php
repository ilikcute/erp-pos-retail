<?php

namespace App\Repositories\Eloquent\Product;

use App\Models\Product\ProductCategory;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ProductCategory::query()
            ->with('parent')
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->where('category_name', 'like', "%{$s}%")
                    ->orWhere('category_code', 'like', "%{$s}%")
            )
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function findById(int $id): ?ProductCategory
    {
        return ProductCategory::with('parent')->find($id);
    }

    public function create(array $data): ProductCategory
    {
        return ProductCategory::create($data);
    }

    public function update(ProductCategory $category, array $data): ProductCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(ProductCategory $category): void
    {
        $category->delete();
    }

    public function listActive(): Collection
    {
        return ProductCategory::active()->orderBy('sort_order')->get();
    }

    public function getTree(): Collection
    {
        // Ambil semua kategori dengan children-nya (recursive via with)
        return ProductCategory::query()
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->orderBy('category_name')
            ->get();
    }

    public function getFlatList(): Collection
    {
        // Flat list untuk dropdown (dengan breadcrumb nama)
        return ProductCategory::query()
            ->orderBy('sort_order')
            ->orderBy('category_name')
            ->get();
    }
}
