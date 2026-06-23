<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\Unit;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUnitRepository implements UnitRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Unit::query()
            ->when(
                $filters['search'] ?? null,
                fn($q, $s) =>
                $q->where(
                    fn($q) => $q
                        ->where('unit_name', 'like', "%{$s}%")
                        ->orWhere('unit_code', 'like', "%{$s}%")
                )
            )
            ->when(isset($filters['is_active']), fn($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Unit
    {
        return Unit::find($id);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);
        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }
}
