<?php

namespace App\Models\Promotion;

use App\Enums\Promotion\PromotionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'promotion_code',
        'promotion_name',
        'description',
        'priority',
        'stackable',
        'valid_from',
        'valid_until',
        'status',
        'earn_point_allowed',
        'redeem_point_allowed',
        'limits',
        'current_usage',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'status' => PromotionStatus::class,
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'stackable' => 'boolean',
        'earn_point_allowed' => 'boolean',
        'redeem_point_allowed' => 'boolean',
        'limits' => 'array',
        'approved_at' => 'datetime',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(PromotionCondition::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(PromotionReward::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PromotionTarget::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(PromotionUsageLog::class);
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();

        return $this->status === PromotionStatus::ACTIVE
            && $this->valid_from <= $now
            && $this->valid_until >= $now;
    }

    public function hasReachedMaxUsage(): bool
    {
        if (! $this->limits || ! isset($this->limits['max_usage'])) {
            return false;
        }

        return $this->current_usage >= $this->limits['max_usage'];
    }

    public function hasCustomerReachedMaxUsage(int $customerId): bool
    {
        if (! $this->limits || ! isset($this->limits['max_usage_per_customer'])) {
            return false;
        }

        $customerUsage = $this->usageLogs()
            ->where('customer_id', $customerId)
            ->count();

        return $customerUsage >= $this->limits['max_usage_per_customer'];
    }
}
