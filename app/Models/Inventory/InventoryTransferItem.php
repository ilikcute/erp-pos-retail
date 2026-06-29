<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\ProductVariant;

class InventoryTransferItem extends Model
{
    protected $table = 'inventory_transfer_items';

    protected $fillable = [
        'transfer_id',
        'inventory_batch_id',
        'product_variant_id',
        'transfer_qty',
    ];

    protected $casts = [
        'transfer_qty' => 'decimal:2',
    ];

    public function transfer()
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
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
