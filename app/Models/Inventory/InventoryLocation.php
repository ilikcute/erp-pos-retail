<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\LocationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_stock_bearing',
        'is_external',
        'parent_id',
        'address',
        'valid_from',
        'valid_to',
        'city',
        'phone',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'type' => LocationType::class,
        'is_stock_bearing' => 'boolean',
        'is_external' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function balances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class, 'location_id');
    }

    public function isStockBearing(): bool
    {
        return $this->is_stock_bearing ?? $this->type->isStockBearing();
    }
}
