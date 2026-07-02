<?php

namespace App\Models\POS;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartVoidLog extends Model
{
    protected $table = 'cart_void_logs';

    protected $fillable = [
        'user_id',
        'cart_id',
        'product_id',
        'qty',
        'reason',
        'voided_at',
    ];

    protected $casts = [
        'qty' => 'integer',
        'voided_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
