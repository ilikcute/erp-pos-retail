<?php

namespace App\Repositories\Eloquent\Pricing;

use App\Models\Pricing\PriceChangeRequest;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPriceChangeRequestRepository implements PriceChangeRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PriceChangeRequest::query()
            ->with(['priceList', 'items'])
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['price_list_id'] ?? null, fn($q, $v) => $q->where('price_list_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?PriceChangeRequest
    {
        return PriceChangeRequest::find($id);
    }

    public function findByIdWithRelations(int $id): ?PriceChangeRequest
    {
        return PriceChangeRequest::with(['priceList', 'items.variant.product'])->find($id);
    }

    public function create(array $data): PriceChangeRequest
    {
        return PriceChangeRequest::create($data);
    }

    public function update(PriceChangeRequest $request, array $data): PriceChangeRequest
    {
        $request->update($data);
        return $request->fresh();
    }

    public function delete(PriceChangeRequest $request): void
    {
        $request->delete();
    }
}
