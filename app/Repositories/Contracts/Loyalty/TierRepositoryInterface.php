<?php

namespace App\Repositories\Contracts\Loyalty;

use Illuminate\Support\Collection;

interface TierRepositoryInterface
{
    public function getAll(): Collection;
    public function determineTier(float $lifetimeSpending, int $lifetimePoints): ?object;
}
