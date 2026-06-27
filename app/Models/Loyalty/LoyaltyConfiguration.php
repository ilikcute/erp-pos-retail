<?php

namespace App\Models\Loyalty;

use Illuminate\Database\Eloquent\Model;

class LoyaltyConfiguration extends Model
{
    protected $fillable = [
        'point_expiry_months',
        'minimum_redeem_points',
        'point_value',
        'earn_rate',
        'allow_negative_point',
        'is_enabled',
        'terms_and_conditions',
        'updated_by',
    ];

    protected $casts = [
        'allow_negative_point' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    /**
     * Singleton pattern - hanya ada 1 konfigurasi
     */
    public static function getInstance(): self
    {
        return self::first() ?? self::create([
            'point_expiry_months' => 12,
            'minimum_redeem_points' => 100,
            'point_value' => 100,
            'earn_rate' => 1000,
            'allow_negative_point' => false,
            'is_enabled' => true,
        ]);
    }
}
