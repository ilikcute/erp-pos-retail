<?php

namespace App\Models\Promotion;

use App\Enums\Promotion\ConditionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionCondition extends Model
{
    protected $fillable = [
        'promotion_id',
        'condition_type',
        'operator',
        'condition_value',
        'description',
    ];

    protected $casts = [
        'condition_type' => ConditionType::class,
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function getDecodedValue(): mixed
    {
        $decoded = json_decode($this->condition_value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->condition_value;
    }
}
