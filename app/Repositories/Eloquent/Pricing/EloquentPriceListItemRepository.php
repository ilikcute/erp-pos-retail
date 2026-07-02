<?php

namespace App\Repositories\Eloquent\Pricing;

use App\Models\Pricing\PriceListItem;
use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPriceListItemRepository implements PriceListItemRepositoryInterface
{
    public function paginateByPriceList(int $priceListId, array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return PriceListItem::where('price_list_id', $priceListId)
            ->with(['variant.product', 'variant.barcodes'])
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->whereHas(
                    'variant',
                    fn ($q) => $q->where('sku', 'like', "%{$s}%")
                        ->orWhereHas(
                            'product',
                            fn ($q) => $q->where('product_name', 'like', "%{$s}%")
                        )
                )
            )
            ->paginate($perPage);
    }

    public function findItem(int $priceListId, int $variantId, ?int $unitId, float $qty): ?PriceListItem
    {
        return PriceListItem::where('price_list_id', $priceListId)
            ->where('product_variant_id', $variantId)
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->where('min_qty', '<=', $qty)
            ->orderByDesc('min_qty')
            ->first();
    }

    public function createOrUpdate(array $search, array $data): PriceListItem
    {
        return PriceListItem::updateOrCreate($search, $data);
    }

    public function delete(PriceListItem $item): void
    {
        $item->delete();
    }
}
