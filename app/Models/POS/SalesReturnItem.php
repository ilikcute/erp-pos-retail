<?php

namespace App\Models\POS;

use App\Models\MasterData\Unit;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'sales_return_id',
        'sales_transaction_item_id',
        'product_variant_id',
        'product_id',
        'item_name',
        'unit_id',
        'quantity',
        'unit_price',
        'tax_amount',
        'line_total',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function salesTransactionItem(): BelongsTo
    {
        return $this->belongsTo(SalesTransactionItem::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
