<?php

namespace App\Repositories\Contracts\Pricing;

use App\Models\Pricing\PriceList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PriceListRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?PriceList;

    public function create(array $data): PriceList;

    public function update(PriceList $priceList, array $data): PriceList;

    public function delete(PriceList $priceList): void;

    public function findActiveDefaultRetail(): ?PriceList;

    public function getActiveMappedToCategory(int $categoryId): Collection;
}
