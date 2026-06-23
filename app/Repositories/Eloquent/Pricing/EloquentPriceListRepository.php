<?php

namespace App\Repositories\Eloquent\Pricing;

use App\Models\Pricing\PriceList;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Enums\PriceListType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentPriceListRepository implements PriceListRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PriceList::query()
            ->with('customerCategories')
            ->when(
                $filters['search'] ?? null,
                fn($q, $s) =>
                $q->where('price_list_name', 'like', "%{$s}%")
                    ->orWhere('price_list_code', 'like', "%{$s}%")
            )
            ->when($filters['type'] ?? null, fn($q, $t) => $q->where('price_list_type', $t))
            ->when(isset($filters['is_active']), fn($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?PriceList
    {
        return PriceList::with('customerCategories')->find($id);
    }

    public function create(array $data): PriceList
    {
        return PriceList::create($data);
    }

    public function update(PriceList $priceList, array $data): PriceList
    {
        $priceList->update($data);
        return $priceList->fresh();
    }

    public function delete(PriceList $priceList): void
    {
        $priceList->delete();
    }

    public function findActiveDefaultRetail(): ?PriceList
    {
        return PriceList::where('is_default', true)
            ->where('price_list_type', PriceListType::RETAIL->value)
            ->active()
            ->first();
    }

    public function getActiveMappedToCategory(int $categoryId): Collection
    {
        return PriceList::whereHas(
            'customerCategories',
            fn($q) =>
            $q->where('customer_category_id', $categoryId)
        )->active()->get();
    }
}
