<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasCreatedBy;
use App\Enums\PriceListType;

class PriceList extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'price_list_code',
        'price_list_name',
        'price_list_type',    // RETAIL|WHOLESALE|SPECIAL
        'currency',           // IDR
        'is_default',         // hanya 1 yang boleh default per type
        'is_active',
        'valid_from',
        'valid_to',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price_list_type' => PriceListType::class,
        'is_default'      => 'boolean',
        'is_active'       => 'boolean',
        'valid_from'      => 'date',
        'valid_to'        => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function customerCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\MasterData\CustomerCategory::class,
            'customer_category_price_lists',
            'price_list_id',
            'customer_category_id'
        )->withTimestamps();
    }

    public function isValid(): bool
    {
        $now = now()->toDateString();
        if ($this->valid_from && $this->valid_from->toDateString() > $now) return false;
        if ($this->valid_to   && $this->valid_to->toDateString()   < $now) return false;
        return $this->is_active;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
