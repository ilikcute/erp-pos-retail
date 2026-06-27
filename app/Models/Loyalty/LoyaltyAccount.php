<?php

namespace App\Models\Loyalty;

use App\Models\MasterData\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $fillable = [
        'account_no',
        'customer_id',
        'current_tier_id',
        'current_balance',
        'lifetime_earned',
        'lifetime_redeemed',
        'lifetime_spending',
        'point_expiry_date',
        'tier_evaluation_date',
        'is_active',
    ];

    protected $casts = [
        'current_balance' => 'integer',
        'lifetime_earned' => 'integer',
        'lifetime_redeemed' => 'integer',
        'lifetime_spending' => 'decimal:2',
        'point_expiry_date' => 'date',
        'tier_evaluation_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'current_tier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(LoyaltyAdjustment::class);
    }

    /**
     * Cek apakah akun bisa redeem sejumlah poin
     */
    public function canRedeem(int $points): bool
    {
        $config = LoyaltyConfiguration::first();
        return $this->current_balance >= $points
            && $points >= ($config->minimum_redeem_points ?? 0);
    }

    /**
     * Hitung nilai Rupiah dari poin
     */
    public function pointsToRupiah(int $points): float
    {
        $config = LoyaltyConfiguration::first();
        return $points * ($config->point_value ?? 0);
    }

    /**
     * Hitung poin dari nilai Rupiah
     */
    public function rupiahToPoints(float $rupiah): int
    {
        $config = LoyaltyConfiguration::first();
        $earnRate = $config->earn_rate ?? 1000;
        return (int) floor($rupiah / $earnRate);
    }
}
