<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number',
        'adjustment_date',
        'adjustment_type',
        'status',
        'reason',
        'rejection_notes',
        'created_by',
        'approved_by',
        'posted_by',
        'approved_at',
        'posted_at'
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class, 'adjustment_id');
    }
}
