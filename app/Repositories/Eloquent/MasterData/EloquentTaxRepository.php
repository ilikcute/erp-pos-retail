<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\Tax;
use App\Repositories\Contracts\MasterData\TaxRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTaxRepository implements TaxRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Tax::query()
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->where(
                    fn ($q) => $q
                        ->where('tax_name', 'like', "%{$s}%")
                        ->orWhere('tax_code', 'like', "%{$s}%")
                )
            )
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Tax
    {
        return Tax::find($id);
    }

    public function create(array $data): Tax
    {
        return Tax::create($data);
    }

    public function update(Tax $tax, array $data): Tax
    {
        $tax->update($data);

        return $tax->fresh();
    }

    public function delete(Tax $tax): void
    {
        $tax->delete();
    }
}
