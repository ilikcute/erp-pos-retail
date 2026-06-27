<?php

namespace App\Models\Inventory;

class InventoryBatch extends Model
{
    protected $fillable = ['product_variant_id', 'location_id', 'batch_no', 'expiry_date', 'unit_cost', 'is_active'];
    protected $casts = ['expiry_date' => 'date', 'is_active' => 'boolean'];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }
}
