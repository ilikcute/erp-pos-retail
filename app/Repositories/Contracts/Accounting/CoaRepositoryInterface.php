<?php

namespace App\Repositories\Contracts\Accounting;

use Illuminate\Support\Collection;

interface CoaRepositoryInterface
{
    public function getAll(array $filters = []): Collection;

    public function getTree(): Collection;

    public function findByCode(string $code): ?object;

    public function findPostableAccounts(): Collection;
}
