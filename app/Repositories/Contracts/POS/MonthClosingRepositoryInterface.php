<?php

namespace App\Repositories\Contracts\POS;

use Illuminate\Support\Collection;

interface MonthClosingRepositoryInterface
{
    public function findByPeriod(int $year, int $month, ?int $locationId = null): ?object;
    public function findById(int $id): ?object;
    public function getAll(array $filters = []): Collection;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function isPeriodLocked(int $year, int $month, ?int $locationId = null): bool;
}