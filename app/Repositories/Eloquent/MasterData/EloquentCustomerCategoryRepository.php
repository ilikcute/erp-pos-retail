<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\CustomerCategory;
use App\Repositories\Contracts\MasterData\CustomerCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentCustomerCategoryRepository implements CustomerCategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CustomerCategory::query()
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->where('category_name', 'like', "%{$s}%")
                    ->orWhere('category_code', 'like', "%{$s}%")
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?CustomerCategory
    {
        return CustomerCategory::find($id);
    }

    public function create(array $data): CustomerCategory
    {
        return CustomerCategory::create($data);
    }

    public function update(CustomerCategory $category, array $data): CustomerCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(CustomerCategory $category): void
    {
        $category->delete();
    }

    public function listAll(): Collection
    {
        return CustomerCategory::orderBy('category_name')->get();
    }
}
