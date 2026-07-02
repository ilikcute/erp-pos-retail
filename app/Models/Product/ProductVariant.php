<?php

namespace App\Models\Product;

use App\Models\Pricing\PriceListItem;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasCreatedBy, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',      // e.g. "Merah - XL"
        'is_default',        // varian utama jika product SIMPLE
        'is_active',
        'weight',            // dalam gram
        'volume',            // dalam ml/cm3
        'purchase_price',    // HPP estimasi / harga beli terakhir
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'weight' => 'decimal:2',
        'volume' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function primaryBarcode()
    {
        return $this->hasOne(ProductBarcode::class)->where('is_primary', true);
    }

    public function variantAttributes(): HasMany
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function costProfile(): HasOne
    {
        return $this->hasOne(ProductCostProfile::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class, 'product_variant_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
