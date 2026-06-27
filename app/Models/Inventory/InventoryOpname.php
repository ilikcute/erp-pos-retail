<?php

namespace App\Models\Inventory;

class InventoryOpname extends Model
{
    protected $fillable = ['opname_number', 'inventory_location_id', 'opname_date', 'status', 'created_by', 'approved_by', 'posted_by', 'approved_at', 'posted_at'];
    protected $casts = ['opname_date' => 'date'];
    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }
    public function items()
    {
        return $this->hasMany(InventoryOpnameItem::class, 'opname_id');
    }
}
