<?php

namespace App\Repositories\Contracts\Inventory;

use Illuminate\Support\Collection;

interface BalanceRepositoryInterface
{
    public function getBalances(array $filters = []): Collection;
    public function getBalance(int $variantId, int $locationId): ?object;
    public function increment(int $variantId, int $locationId, float $qty): object;
    public function decrement(int $variantId, int $locationId, float $qty): object;
    public function reserve(int $variantId, int $locationId, float $qty): object;
    public function releaseReservation(int $variantId, int $locationId, float $qty): object;
}
