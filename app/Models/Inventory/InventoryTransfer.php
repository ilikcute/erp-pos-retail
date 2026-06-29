<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'source_location_id',
        'destination_location_id',
        'transfer_date',
        'status',
        'remarks',
        'created_by',
        'posted_by',
        'posted_at'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'posted_at' => 'datetime'
    ];

    public function source()
    {
        return $this->belongsTo(InventoryLocation::class, 'source_location_id');
    }

    public function destination()
    {
        return $this->belongsTo(InventoryLocation::class, 'destination_location_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }
}
