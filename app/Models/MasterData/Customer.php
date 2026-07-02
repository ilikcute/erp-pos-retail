<?php

namespace App\Models\MasterData;

use App\Models\Loyalty\LoyaltyAccount;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasCreatedBy, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'customer_name',
        'customer_category_id',
        'phone',
        'email',
        'address',
        'city',
        'birth_date',
        'gender',
        'tax_id',
        'credit_limit',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'birth_date' => 'date',
    ];

    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(LoyaltyAccount::class, 'customer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
