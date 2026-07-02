<?php

namespace App\Repositories\Eloquent\POS;

use App\Models\POS\Shift;
use App\Repositories\Contracts\POS\ShiftRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentShiftRepository implements ShiftRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Shift::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('shift_code', 'like', "%{$search}%")
                        ->orWhere('shift_name', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Shift
    {
        return Shift::find($id);
    }

    public function findActive(): Collection
    {
        return Shift::active()->orderBy('shift_name')->get();
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);

        return $shift->fresh();
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }
}
