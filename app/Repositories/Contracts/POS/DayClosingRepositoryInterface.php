<?php

namespace App\Repositories\Contracts\POS;

use Illuminate\Support\Collection;

interface DayClosingRepositoryInterface
{
    public function findByDate(string $date, ?int $locationId = null): ?object;
    public function findById(int $id): ?object;
    public function getAll(array $filters = []): Collection;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function generateClosingNumber(): string;
}
