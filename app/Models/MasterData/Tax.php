<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasCreatedBy;

class Tax extends Model
{
    use SoftDeletes, HasCreatedBy;

    protected $fillable = [
        'tax_code',
        'tax_name',
        'tax_rate',        // e.g. 11.00 for PPN 11%
        'is_inclusive',    // apakah harga sudah termasuk pajak
        'account_id',      // FK ke chart_of_accounts (diisi setelah modul Accounting ready)
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tax_rate'     => 'decimal:2',
        'is_inclusive' => 'boolean',
        'is_active'    => 'boolean',
    ];

    /**
     * Hitung nilai pajak dari amount.
     */
    public function calculateTax(float $amount): float
    {
        if ($this->is_inclusive) {
            // Harga sudah termasuk pajak: tax = amount - (amount / (1 + rate/100))
            return $amount - ($amount / (1 + $this->tax_rate / 100));
        }

        // Harga belum termasuk pajak: tax = amount * rate/100
        return $amount * ($this->tax_rate / 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
