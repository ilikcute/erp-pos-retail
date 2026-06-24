<?php

namespace App\Repositories\Contracts\POS;

use App\Models\POS\SalesTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SalesTransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?SalesTransaction;

    public function findBySession(int $sessionId, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): SalesTransaction;

    public function update(SalesTransaction $transaction, array $data): SalesTransaction;

    public function getDailySummary(\Carbon\Carbon $date): array;
}
