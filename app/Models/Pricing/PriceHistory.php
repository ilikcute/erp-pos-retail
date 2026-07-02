<?php

namespace App\Models\Pricing;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable price history — insert only, never update/delete.
 */
class PriceHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'price_list_id',
        'product_variant_id',
        'unit_id',
        'old_price',
        'new_price',
        'changed_by',
        'change_source',      // MANUAL|PRICE_CHANGE_REQUEST|IMPORT
        'price_change_request_id',
        'notes',
        'changed_at',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
