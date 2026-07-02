<?php

namespace App\Repositories\Eloquent\Loyalty;

use App\Models\Loyalty\LoyaltyAccount;
use App\Models\Loyalty\LoyaltyConfiguration;
use App\Repositories\Contracts\Loyalty\AccountRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AccountRepository implements AccountRepositoryInterface
{
    public function findByCustomerId(int $customerId): ?object
    {
        return LoyaltyAccount::with('tier')
            ->where('customer_id', $customerId)
            ->first();
    }

    public function findOrCreateForCustomer(int $customerId): object
    {
        return DB::transaction(function () use ($customerId) {
            $account = LoyaltyAccount::where('customer_id', $customerId)->first();

            if (! $account) {
                $account = LoyaltyAccount::create([
                    'account_no' => $this->generateAccountNo(),
                    'customer_id' => $customerId,
                    'current_balance' => 0,
                    'lifetime_earned' => 0,
                    'lifetime_redeemed' => 0,
                    'lifetime_spending' => 0,
                    'is_active' => true,
                ]);
            }

            return $account->load('tier');
        });
    }

    public function addPoints(int $accountId, int $points): object
    {
        return DB::transaction(function () use ($accountId, $points) {
            $account = LoyaltyAccount::lockForUpdate()->findOrFail($accountId);
            $account->increment('current_balance', $points);

            return $account->fresh();
        });
    }

    public function deductPoints(int $accountId, int $points): object
    {
        return DB::transaction(function () use ($accountId, $points) {
            $account = LoyaltyAccount::lockForUpdate()->findOrFail($accountId);

            $config = LoyaltyConfiguration::getInstance();
            if (! $config->allow_negative_point && $account->current_balance < $points) {
                throw new \DomainException(
                    "Poin tidak mencukupi. Tersedia: {$account->current_balance}, dibutuhkan: {$points}"
                );
            }

            $account->decrement('current_balance', $points);

            return $account->fresh();
        });
    }

    public function updateLifetimeStats(int $accountId, float $spending, int $pointsEarned): object
    {
        return DB::transaction(function () use ($accountId, $spending, $pointsEarned) {
            $account = LoyaltyAccount::lockForUpdate()->findOrFail($accountId);
            $account->increment('lifetime_spending', $spending);
            $account->increment('lifetime_earned', $pointsEarned);

            return $account->fresh();
        });
    }

    private function generateAccountNo(): string
    {
        $last = LoyaltyAccount::orderBy('id', 'desc')->first();
        $nextNumber = $last ? $last->id + 1 : 1;

        return 'LYL-'.str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
    }
}
