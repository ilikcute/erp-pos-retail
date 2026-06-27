<?php

namespace App\Repositories\Contracts\Inventory;

use Illuminate\Support\Collection;

interface LedgerRepositoryInterface
{
    public function create(array $data): object;
    public function getLedgers(array $filters = []): Collection;
    public function generateReferenceNumber(string $prefix): string;
}
