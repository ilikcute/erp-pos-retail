<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCreatedBy;

class ProductBrand extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'brand_code',
        'brand_name',
        'description',
        'logo',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'code',
        'name',
    ];

    public function getCodeAttribute(): string
    {
        return $this->brand_code;
    }

    public function getNameAttribute(): string
    {
        return $this->brand_name;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
