<?php

namespace App\Models\Inventory;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class InventoryOpnameItem extends Model
{
    protected $table = 'inventory_opname_items';

    protected $fillable = [
        'opname_id',
        'inventory_batch_id',
        'product_variant_id',
        'system_qty',
        'physical_qty',
        'difference',
    ];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'physical_qty' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function opname()
    {
        return $this->belongsTo(InventoryOpname::class, 'opname_id');
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
