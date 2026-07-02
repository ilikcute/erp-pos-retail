<?php

namespace App\Models\Pricing;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceChangeRequestItem extends Model
{
    protected $fillable = [
        'price_change_request_id',
        'product_variant_id',
        'unit_id',
        'old_price',
        'new_price',
        'change_reason',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(PriceChangeRequest::class, 'price_change_request_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getChangePctAttribute(): float
    {
        if (! $this->old_price || $this->old_price == 0) {
            return 0;
        }

        return round((($this->new_price - $this->old_price) / $this->old_price) * 100, 2);
    }
}
