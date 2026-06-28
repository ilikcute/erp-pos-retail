<?php

namespace App\Models\Promotion;

use App\Enums\Promotion\MarginProtectionMode;
use Illuminate\Database\Eloquent\Model;

class PromotionSetting extends Model
{
    protected $fillable = [
        'margin_protection_mode',
        'allow_negative_margin',
        'allow_stacking',
        'max_stacking_promotions',
        'updated_by',
    ];

    protected $casts = [
        'margin_protection_mode' => MarginProtectionMode::class,
        'allow_negative_margin' => 'boolean',
        'allow_stacking' => 'boolean',
    ];

    public static function getInstance(): self
    {
        return self::first() ?? self::create([
            'margin_protection_mode' => MarginProtectionMode::WARNING,
            'allow_negative_margin' => false,
            'allow_stacking' => false,
            'max_stacking_promotions' => 3,
        ]);
    }
}
