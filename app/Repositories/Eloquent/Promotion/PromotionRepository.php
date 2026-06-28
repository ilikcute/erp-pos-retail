<?php

namespace App\Repositories\Eloquent\Promotion;

use App\Enums\Promotion\PromotionStatus;
use App\Models\Promotion\Promotion;
use App\Models\Promotion\PromotionUsageLog;
use App\Repositories\Contracts\Promotion\PromotionRepositoryInterface;
use Illuminate\Support\Collection;

class PromotionRepository implements PromotionRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = Promotion::with(['conditions', 'rewards', 'targets']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['valid_date'])) {
            $date = $filters['valid_date'];
            $query->where('valid_from', '<=', $date)
                ->where('valid_until', '>=', $date);
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findActivePromotions(?string $validDate = null): Collection
    {
        $now = $validDate ? \Carbon\Carbon::parse($validDate) : now();

        return Promotion::with(['conditions', 'rewards', 'targets'])
            ->where('status', PromotionStatus::ACTIVE)
            ->where('valid_from', '<=', $now)
            ->where('valid_until', '>=', $now)
            ->where(function ($query) {
                $query->whereNull('limits')
                    ->orWhereRaw("JSON_EXTRACT(limits, '$.max_usage') IS NULL")
                    ->orWhereRaw("current_usage < JSON_EXTRACT(limits, '$.max_usage')");
            })
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function findById(int $id): ?object
    {
        return Promotion::with(['conditions', 'rewards', 'targets'])->find($id);
    }

    public function findByCode(string $code): ?object
    {
        return Promotion::with(['conditions', 'rewards', 'targets'])
            ->where('promotion_code', $code)
            ->first();
    }

    public function getCustomerUsageCount(int $promotionId, int $customerId): int
    {
        return PromotionUsageLog::where('promotion_id', $promotionId)
            ->where('customer_id', $customerId)
            ->count();
    }
}
