<?php

namespace App\Repositories\Contracts\POS;

use App\Models\POS\Shift;
use Illuminate\Database\Eloquent\Collection;

interface ShiftRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function findById(int $id): ?Shift;

    public function findActive(): Collection;

    public function create(array $data): Shift;

    public function update(Shift $shift, array $data): Shift;

    public function delete(Shift $shift): void;
}
