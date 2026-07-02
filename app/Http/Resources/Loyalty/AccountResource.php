<?php

namespace App\Http\Resources\Loyalty;

use App\Models\Loyalty\LoyaltyConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data' => [
                'account_no' => $this->account_no,
                'customer_id' => $this->customer_id,
                'customer_name' => $this->customer?->customer_name,
                'current_balance' => $this->current_balance,
                'lifetime_earned' => $this->lifetime_earned,
                'lifetime_redeemed' => $this->lifetime_redeemed,
                'lifetime_spending' => (float) $this->lifetime_spending,
                'membership_tier' => $this->tier?->tier_code,
                'tier_name' => $this->tier?->tier_name,
                'point_multiplier' => (float) ($this->tier?->point_multiplier ?? 1.0),
                'point_expiry_date' => $this->point_expiry_date?->format('Y-m-d'),
                'tier_evaluation_date' => $this->tier_evaluation_date?->format('Y-m-d'),
                'is_active' => $this->is_active,
                'rupiah_value' => $this->current_balance * (LoyaltyConfiguration::getInstance()->point_value ?? 0),
            ],
        ];
    }
}
