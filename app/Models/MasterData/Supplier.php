<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasCreatedBy;

class Supplier extends Model
{
    use SoftDeletes, HasCreatedBy;

    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'postal_code',
        'tax_id',           // NPWP supplier
        'payment_term_days',
        'credit_limit',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'credit_limit'      => 'decimal:2',
        'payment_term_days' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
