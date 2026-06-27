<?php

namespace App\Models\POS;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class HeldCartItem extends Model
{
    protected $fillable = ['held_cart_id', 'product_id', 'qty', 'price'];

    public function heldCart()
    {
        return $this->belongsTo(HeldCart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
