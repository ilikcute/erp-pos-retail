<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryOpname extends Model
{
    protected $table = 'inventory_opnames';

    protected $fillable = [
        'opname_number',
        'inventory_location_id',
        'opname_date',
        'status',
        'created_by',
        'approved_by',
        'posted_by',
        'approved_at',
        'posted_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryOpnameItem::class, 'opname_id');
    }
}
