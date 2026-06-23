<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCreatedBy;

class PriceListItem extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'price_list_id',
        'product_variant_id',
        'unit_id',            // FK ke units — harga per satuan ini
        'price',
        'min_qty',            // minimum qty untuk harga ini (tiered pricing)
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price'   => 'decimal:2',
        'min_qty' => 'decimal:4',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product\ProductVariant::class, 'product_variant_id');
    }
}
