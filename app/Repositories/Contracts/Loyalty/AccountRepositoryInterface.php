<?php

namespace App\Repositories\Contracts\Loyalty;

interface AccountRepositoryInterface
{
    public function findByCustomerId(int $customerId): ?object;

    public function findOrCreateForCustomer(int $customerId): object;

    public function addPoints(int $accountId, int $points): object;

    public function deductPoints(int $accountId, int $points): object;

    public function updateLifetimeStats(int $accountId, float $spending, int $pointsEarned): object;
}
