<?php

namespace App\Repositories\Contracts\MasterData;

use App\Models\MasterData\Tax;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaxRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Tax;

    public function create(array $data): Tax;

    public function update(Tax $tax, array $data): Tax;

    public function delete(Tax $tax): void;
}
