<?php

namespace App\Repositories\Contracts\Accounting;

use Illuminate\Support\Collection;

interface PaymentMethodRepositoryInterface
{
    public function getAll(array $filters = []): Collection;

    public function findActive(): Collection;

    public function findByCode(string $code): ?object;

    public function findByType(string $type): ?object;
}
