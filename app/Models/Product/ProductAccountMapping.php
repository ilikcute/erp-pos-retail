<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCreatedBy;

/**
 * Mapping akun Accounting per produk.
 * account_id FK ke chart_of_accounts (diisi saat Phase 6).
 */
class ProductAccountMapping extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'product_id',
        'inventory_account_id',   // akun aset persediaan
        'cogs_account_id',        // akun HPP
        'sales_account_id',       // akun pendapatan penjualan
        'return_account_id',      // akun retur penjualan
        'created_by',
        'updated_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
