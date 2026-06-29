<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\ProductVariant;
use App\Models\Inventory\InventoryBatch;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_variant_id',
        'inventory_batch_id',
        'received_qty',
        'unit_cost',
        'batch_no',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'received_qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function receipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
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
