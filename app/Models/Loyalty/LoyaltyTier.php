<?php

namespace App\Models\Loyalty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    protected $fillable = [
        'tier_code',
        'tier_name',
        'minimum_spending',
        'minimum_points',
        'point_multiplier',
        'discount_percentage',
        'benefits',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'minimum_spending' => 'decimal:2',
        'point_multiplier' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(LoyaltyAccount::class, 'current_tier_id');
    }
}
