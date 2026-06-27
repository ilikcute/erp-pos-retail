<?php

namespace App\Models\Inventory;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $fillable = [
        'product_variant_id',
        'location_id',
        'qty_on_hand',
        'qty_reserved',
        'qty_available',
        'last_movement_at',
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:2',
        'qty_reserved' => 'decimal:2',
        'qty_available' => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /**
     * Recalculate qty_available dari on_hand - reserved
     */
    public function recalculate(): void
    {
        $this->qty_available = $this->qty_on_hand - $this->qty_reserved;
        $this->save();
    }
}
