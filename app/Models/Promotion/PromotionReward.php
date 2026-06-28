<?php

namespace App\Models\Promotion;

use App\Enums\Promotion\RewardType;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionReward extends Model
{
    protected $fillable = [
        'promotion_id',
        'reward_type',
        'reward_value',
        'max_discount',
        'free_product_id',
        'free_product_qty',
    ];

    protected $casts = [
        'reward_type' => RewardType::class,
        'reward_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function freeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }
}
