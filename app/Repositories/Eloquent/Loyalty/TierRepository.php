<?php

namespace App\Repositories\Eloquent\Loyalty;

use App\Models\Loyalty\LoyaltyTier;
use App\Repositories\Contracts\Loyalty\TierRepositoryInterface;
use Illuminate\Support\Collection;

class TierRepository implements TierRepositoryInterface
{
    public function getAll(): Collection
    {
        return LoyaltyTier::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function determineTier(float $lifetimeSpending, int $lifetimePoints): ?object
    {
        return LoyaltyTier::where('is_active', true)
            ->where('minimum_spending', '<=', $lifetimeSpending)
            ->where('minimum_points', '<=', $lifetimePoints)
            ->orderBy('sort_order', 'desc')
            ->first();
    }
}
