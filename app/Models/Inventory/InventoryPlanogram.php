<?php

namespace App\Models\Inventory;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class InventoryPlanogram extends Model
{
    protected $table = 'inventory_planograms';

    protected $fillable = ['product_variant_id', 'location_id', 'position_code', 'notes', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }
}
