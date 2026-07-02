<?php

namespace App\Models\POS;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'shift_code',
        'shift_name',
        'start_time',
        'end_time',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function salesSessions(): HasMany
    {
        return $this->hasMany(SalesSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
