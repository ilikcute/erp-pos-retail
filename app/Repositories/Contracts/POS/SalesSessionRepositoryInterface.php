<?php

namespace App\Repositories\Contracts\POS;

use App\Models\POS\SalesSession;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SalesSessionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?SalesSession;

    public function findOpenByCashier(int $cashierId): ?SalesSession;

    public function findOpenByDate(Carbon $date): Collection;

    public function create(array $data): SalesSession;

    public function update(SalesSession $session, array $data): SalesSession;

    public function closeSession(SalesSession $session, array $closingData): SalesSession;
}
