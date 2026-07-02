<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\TransactionType;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryLedger extends Model
{
    protected $fillable = [
        'reference_number',
        'transaction_type',
        'product_variant_id',
        'location_id',
        'inventory_batch_id',
        'qty_change',
        'qty_before',
        'qty_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_type' => TransactionType::class,
        'transaction_date' => 'datetime',
        'qty_change' => 'decimal:2',
        'qty_before' => 'decimal:2',
        'qty_after' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
