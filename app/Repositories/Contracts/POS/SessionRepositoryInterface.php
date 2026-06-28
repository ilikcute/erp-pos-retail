<?php

namespace App\Repositories\Contracts\POS;

use Illuminate\Support\Collection;

interface SessionRepositoryInterface
{
    public function findActiveSession(int $userId): ?object;
    public function findById(int $id): ?object;
    public function getAll(array $filters = []): Collection;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function hasOpenSession(int $userId): bool;
}
