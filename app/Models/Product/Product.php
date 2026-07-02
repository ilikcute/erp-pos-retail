<?php

namespace App\Models\Product;

use App\Enums\ProductType;
use App\Models\MasterData\Tax;
use App\Models\Pricing\PriceListItem;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasCreatedBy, SoftDeletes;

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
        'tax_id',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
        'is_active' => 'boolean',
        'is_sellable' => 'boolean',
        'is_purchasable' => 'boolean',
        'track_stock' => 'boolean',
        'min_stock' => 'decimal:4',
        'max_stock' => 'decimal:4',
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

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function priceListItems()
    {
        return $this->hasManyThrough(
            PriceListItem::class,
            ProductVariant::class,
            'product_id',
            'product_variant_id',
            'id',
            'id'
        );
    }

    public function barcodes()
    {
        return $this->hasManyThrough(
            ProductBarcode::class,
            ProductVariant::class,
            'product_id',            // FK di product_variants → products
            'product_variant_id',    // FK di product_barcodes → product_variants
            'id',                    // Local key di products
            'id'                     // Local key di product_variants
        );
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true)->orderBy('sort_order');
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

    /**
     * Accessor for title to maintain compatibility with UI expecting `title`.
     */
    public function getTitleAttribute(): string
    {
        return $this->product_name;
    }
}
