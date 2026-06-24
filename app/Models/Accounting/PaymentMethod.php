<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCreatedBy;

class PaymentMethod extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'code',
        'name',
        'type',
        'account_id',
        'is_active',
        'sort_order',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCash($query)
    {
        return $query->where('type', 'CASH');
    }

    public function scopeNonCash($query)
    {
        return $query->where('type', '!=', 'CASH');
    }
}
