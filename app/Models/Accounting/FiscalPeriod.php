<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\System\User;

class FiscalPeriod extends Model
{
    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'status',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function closer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\System\User::class, 'closed_by');
    }

    public function trialBalances(): HasMany
    {
        return $this->hasMany(TrialBalance::class);
    }
}
