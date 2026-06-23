<?php

namespace App\Repositories\Contracts\Pricing;

use App\Models\Pricing\PriceHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PriceHistoryRepositoryInterface
{
    public function paginateByVariant(int $variantId, int $perPage = 25): LengthAwarePaginator;

    public function create(array $data): PriceHistory;
}
