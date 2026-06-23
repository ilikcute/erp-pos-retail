<?php

namespace App\Repositories\Contracts\Pricing;

use App\Models\Pricing\PriceListItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PriceListItemRepositoryInterface
{
    public function paginateByPriceList(int $priceListId, array $filters = [], int $perPage = 50): LengthAwarePaginator;

    public function findItem(int $priceListId, int $variantId, ?int $unitId, float $qty): ?PriceListItem;

    public function createOrUpdate(array $search, array $data): PriceListItem;

    public function delete(PriceListItem $item): void;
}
