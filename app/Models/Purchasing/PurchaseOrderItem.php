<?php

namespace App\Models\Purchasing;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'ordered_qty',
        'received_qty',
        'unit_cost',
        'discount_amount',
        'tax_amount',
        'line_total',
        'notes',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
