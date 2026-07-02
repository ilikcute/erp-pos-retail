<?php

namespace App\Models\Purchasing;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'product_variant_id',
        'requested_qty',
        'notes',
    ];

    protected $casts = [
        'requested_qty' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
