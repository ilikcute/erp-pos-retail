<?php

namespace App\Models\Product;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Profil biaya per varian — digunakan oleh Inventory untuk HPP.
 * cost_method: FIFO|AVERAGE
 */
class ProductCostProfile extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'product_variant_id',
        'cost_method',     // FIFO|AVERAGE
        'standard_cost',   // HPP standar (manual override jika tidak auto-FIFO)
        'last_cost',       // HPP dari GR terakhir
        'average_cost',    // HPP rata-rata bergerak
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'standard_cost' => 'decimal:4',
        'last_cost' => 'decimal:4',
        'average_cost' => 'decimal:4',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
