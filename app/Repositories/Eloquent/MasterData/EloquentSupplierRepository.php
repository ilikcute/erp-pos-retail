<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\Supplier;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentSupplierRepository implements SupplierRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['is_active']), fn($q) => $q->where('is_active', $filters['is_active']))
            ->when($filters['city'] ?? null, fn($q, $city) => $q->where('city', $city))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Supplier
    {
        return Supplier::find($id);
    }

    public function findByCode(string $code): ?Supplier
    {
        return Supplier::where('supplier_code', $code)->first();
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier->fresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    public function listActive(): Collection
    {
        return Supplier::active()->orderBy('supplier_name')->get();
    }
}
