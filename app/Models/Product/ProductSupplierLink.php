<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MasterData\Supplier;
use App\Traits\HasCreatedBy;

class ProductSupplierLink extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'supplier_sku',          // kode produk di sistem supplier
        'supplier_product_name',
        'min_order_qty',
        'is_preferred',          // supplier utama untuk produk ini
        'lead_time_days',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_preferred'   => 'boolean',
        'min_order_qty'  => 'decimal:4',
        'lead_time_days' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
