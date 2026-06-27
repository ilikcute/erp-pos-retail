<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCreatedBy;

class ProductImage extends Model
{
    use HasCreatedBy;
    protected $table = 'product_images';
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    public function getImageAttribute()
    {
        return $this->image_path;
    }
}
