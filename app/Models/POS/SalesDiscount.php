<?php

namespace App\Models\POS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCreatedBy;

class SalesDiscount extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'sales_transaction_id',
        'sales_transaction_item_id',
        'discount_type',
        'discount_value',
        'discount_amount',
        'promotion_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function salesTransactionItem(): BelongsTo
    {
        return $this->belongsTo(SalesTransactionItem::class);
    }
}
