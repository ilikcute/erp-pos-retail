<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\ProductVariant;

class InventoryAdjustmentItem extends Model
{
    protected $table = 'inventory_adjustment_items';

    protected $fillable = [
        'adjustment_id',
        'inventory_batch_id',
        'product_variant_id',
        'adjustment_qty',
        'notes',
    ];

    protected $casts = [
        'adjustment_qty' => 'decimal:2',
    ];

    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'adjustment_id');
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
