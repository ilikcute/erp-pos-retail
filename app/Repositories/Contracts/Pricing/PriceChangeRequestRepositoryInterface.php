<?php

namespace App\Repositories\Contracts\Pricing;

use App\Models\Pricing\PriceChangeRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PriceChangeRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?PriceChangeRequest;

    public function findByIdWithRelations(int $id): ?PriceChangeRequest;

    public function create(array $data): PriceChangeRequest;

    public function update(PriceChangeRequest $request, array $data): PriceChangeRequest;

    public function delete(PriceChangeRequest $request): void;
}
