<?php

namespace App\Models\Pricing;

use App\Enums\PriceChangeRequestStatus;
use App\Traits\HasApprovedBy;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceChangeRequest extends Model
{
    use HasApprovedBy, HasCreatedBy;

    protected $fillable = [
        'request_no',
        'price_list_id',
        'status',
        'effective_date',
        'reason',
        'notes',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'applied_at',
    ];

    protected $casts = [
        'status' => PriceChangeRequestStatus::class,
        'effective_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceChangeRequestItem::class);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === PriceChangeRequestStatus::DRAFT
            && $this->items()->exists();
    }
}
