<?php

namespace App\Repositories\Contracts\Promotion;

use Illuminate\Support\Collection;

interface PromotionRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function findActivePromotions(?string $validDate = null): Collection;
    public function findById(int $id): ?object;
    public function findByCode(string $code): ?object;
    public function getCustomerUsageCount(int $promotionId, int $customerId): int;
}
