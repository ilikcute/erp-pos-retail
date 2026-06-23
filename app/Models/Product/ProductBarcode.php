<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCreatedBy;

class ProductBarcode extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'product_variant_id',
        'barcode',
        'barcode_type',   // EAN13|EAN8|QR|CODE128|CUSTOM
        'is_primary',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
