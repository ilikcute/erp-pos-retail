<?php

namespace App\Repositories\Eloquent\Pricing;

use App\Models\Pricing\PriceHistory;
use App\Repositories\Contracts\Pricing\PriceHistoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPriceHistoryRepository implements PriceHistoryRepositoryInterface
{
    public function paginateByVariant(int $variantId, int $perPage = 25): LengthAwarePaginator
    {
        return PriceHistory::where('product_variant_id', $variantId)
            ->with('priceList')
            ->latest('changed_at')
            ->paginate($perPage);
    }

    public function create(array $data): PriceHistory
    {
        return PriceHistory::create($data);
    }
}
