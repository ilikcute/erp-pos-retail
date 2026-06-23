<?php

namespace App\Repositories\Contracts\MasterData;

use App\Models\MasterData\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UnitRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Unit;

    public function create(array $data): Unit;

    public function update(Unit $unit, array $data): Unit;

    public function delete(Unit $unit): void;
}
