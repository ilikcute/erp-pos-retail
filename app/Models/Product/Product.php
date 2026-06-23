<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasCreatedBy;
use App\Enums\ProductType;

class Product extends Model
{
    use SoftDeletes, HasCreatedBy;

    protected $fillable = [
        'product_code',
        'product_name',
        'product_type',       // SIMPLE|VARIANT|BUNDLE
        'brand_id',
        'category_id',
        'base_unit_id',       // FK ke units
        'description',
        'short_description',
        'is_active',
        'is_sellable',        // bisa dijual di POS
        'is_purchasable',     // bisa dibeli dari supplier
        'track_stock',        // apakah stok dilacak
        'min_stock',          // minimum stok sebelum alert
        'max_stock',
        'reorder_point',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'product_type'  => ProductType::class,
        'is_active'     => 'boolean',
        'is_sellable'   => 'boolean',
        'is_purchasable' => 'boolean',
        'track_stock'   => 'boolean',
        'min_stock'     => 'decimal:4',
        'max_stock'     => 'decimal:4',
        'reorder_point' => 'decimal:4',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('sort_order');
    }

    public function supplierLinks(): HasMany
    {
        return $this->hasMany(ProductSupplierLink::class);
    }

    public function accountMapping(): HasOne
    {
        return $this->hasOne(ProductAccountMapping::class);
    }

    // ─── Business Logic ───────────────────────────────────────────────

    public function isSellable(): bool
    {
        return $this->is_active && $this->is_sellable;
    }

    public function hasVariants(): bool
    {
        return $this->product_type === ProductType::VARIANT;
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSellable($query)
    {
        return $query->where('is_active', true)->where('is_sellable', true);
    }

    public function scopePurchasable($query)
    {
        return $query->where('is_active', true)->where('is_purchasable', true);
    }
}
