<?php

namespace App\Models\Purchasing;

use App\Models\Inventory\InventoryBatch;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'goods_receipt_item_id',
        'product_variant_id',
        'inventory_batch_id',
        'return_qty',
        'unit_cost',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'return_qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function goodsReceiptItem()
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
