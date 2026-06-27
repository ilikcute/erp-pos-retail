<?php

namespace App\Models\Loyalty;

use App\Enums\Loyalty\RewardType;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyRewardCatalog extends Model
{
    protected $fillable = [
        'reward_code',
        'reward_name',
        'reward_type',
        'point_required',
        'voucher_amount',
        'discount_percentage',
        'product_id',
        'stock_qty',
        'redeemed_qty',
        'valid_from',
        'valid_until',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'reward_type' => RewardType::class,
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(LoyaltyRedemption::class, 'reward_catalog_id');
    }

    public function isAvailable(): bool
    {
        $now = now();
        return $this->is_active
            && $this->stock_qty > $this->redeemed_qty
            && ($this->valid_from === null || $this->valid_from <= $now)
            && ($this->valid_until === null || $this->valid_until >= $now);
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->stock_qty - $this->redeemed_qty;
    }
}
