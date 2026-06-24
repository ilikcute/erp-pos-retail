<?php

namespace App\Repositories\Contracts\POS;

use App\Models\POS\SalesHold;
use Illuminate\Database\Eloquent\Collection;

interface SalesHoldRepositoryInterface
{
    public function findById(int $id): ?SalesHold;

    public function findActiveBySession(int $sessionId): Collection;

    public function findActiveByCashier(int $cashierId): Collection;

    public function create(array $data): SalesHold;

    public function update(SalesHold $hold, array $data): SalesHold;

    public function resume(SalesHold $hold): void;

    public function cancel(SalesHold $hold): void;
}
