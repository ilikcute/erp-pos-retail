<?php

namespace App\Services\Loyalty;

use App\Enums\Loyalty\AdjustmentType;
use App\Enums\Loyalty\RedemptionStatus;
use App\Enums\Loyalty\RewardType;
use App\Enums\Loyalty\TransactionType;
use App\Models\Loyalty\LoyaltyAccount;
use App\Models\Loyalty\LoyaltyAdjustment;
use App\Models\Loyalty\LoyaltyConfiguration;
use App\Models\Loyalty\LoyaltyRedemption;
use App\Models\Loyalty\LoyaltyRewardCatalog;
use App\Models\Loyalty\LoyaltyTransaction;
use App\Repositories\Contracts\Loyalty\AccountRepositoryInterface;
use App\Repositories\Contracts\Loyalty\TierRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepo,
        private readonly TierRepositoryInterface $tierRepo,
    ) {}

    // ═══════════════════════════════════════════════════════════
    // EARN POINTS (saat transaksi POS)
    // ═══════════════════════════════════════════════════════════
    public function earnPoints(
        int $customerId,
        float $transactionValue,
        $reference = null,
        ?int $userId = null,
    ): array {
        $config = LoyaltyConfiguration::getInstance();

        if (! $config->is_enabled) {
            return ['earned' => 0, 'message' => 'Loyalty program nonaktif'];
        }

        return DB::transaction(function () use ($customerId, $transactionValue, $reference, $userId) {
            $account = $this->accountRepo->findOrCreateForCustomer($customerId);
            $config = LoyaltyConfiguration::getInstance();

            // Hitung poin dasar
            $basePoints = (int) floor($transactionValue / $config->earn_rate);

            // Apply multiplier dari tier
            $multiplier = $account->tier?->point_multiplier ?? 1.0;
            $earnedPoints = (int) floor($basePoints * $multiplier);

            if ($earnedPoints <= 0) {
                return ['earned' => 0, 'message' => 'Transaksi terlalu kecil untuk dapat poin'];
            }

            // Update balance
            $balanceBefore = $account->current_balance;
            $this->accountRepo->addPoints($account->id, $earnedPoints);
            $this->accountRepo->updateLifetimeStats($account->id, $transactionValue, $earnedPoints);

            // Update expiry date
            $expiryDate = now()->addMonths($config->point_expiry_months);
            $account->update(['point_expiry_date' => $expiryDate]);

            // Catat ke ledger
            $this->recordTransaction(
                account: $account,
                type: TransactionType::EARN,
                points: $earnedPoints,
                balanceBefore: $balanceBefore,
                transactionValue: $transactionValue,
                reference: $reference,
                userId: $userId,
                notes: 'Perolehan dari transaksi Rp '.number_format($transactionValue, 0, ',', '.')
            );

            // Cek upgrade tier
            $this->evaluateTierUpgrade($account);

            return [
                'earned' => $earnedPoints,
                'balance' => $account->fresh()->current_balance,
                'tier' => $account->fresh()->tier?->tier_code,
                'message' => "Berhasil mendapatkan {$earnedPoints} poin",
            ];
        });
    }

    // ═══════════════════════════════════════════════════════════
    // REDEEM POINTS (tukar ke reward catalog)
    // ═══════════════════════════════════════════════════════════
    public function redeemReward(
        int $customerId,
        int $rewardCatalogId,
        ?int $userId = null,
    ): LoyaltyRedemption {
        return DB::transaction(function () use ($customerId, $rewardCatalogId, $userId) {
            $account = $this->accountRepo->findOrCreateForCustomer($customerId);
            $reward = LoyaltyRewardCatalog::findOrFail($rewardCatalogId);

            if (! $reward->isAvailable()) {
                throw new \DomainException('Reward tidak tersedia atau stok habis');
            }

            if (! $account->canRedeem($reward->point_required)) {
                throw new \DomainException(
                    "Poin tidak mencukupi. Dibutuhkan: {$reward->point_required}, tersedia: {$account->current_balance}"
                );
            }

            // Kurangi poin
            $balanceBefore = $account->current_balance;
            $this->accountRepo->deductPoints($account->id, $reward->point_required);
            $account->increment('lifetime_redeemed', $reward->point_required);

            // Buat redemption
            $redemption = LoyaltyRedemption::create([
                'redemption_number' => $this->generateRedemptionNumber(),
                'loyalty_account_id' => $account->id,
                'reward_catalog_id' => $reward->id,
                'points_used' => $reward->point_required,
                'reward_value' => $reward->voucher_amount ?? 0,
                'status' => RedemptionStatus::REDEEMED,
                'voucher_code' => $reward->reward_type === RewardType::VOUCHER
                    ? $this->generateVoucherCode()
                    : null,
                'voucher_expiry' => $reward->reward_type === RewardType::VOUCHER
                    ? now()->addMonths(3)
                    : null,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            // Kurangi stok reward
            $reward->increment('redeemed_qty', 1);

            // Catat ke ledger
            $this->recordTransaction(
                account: $account,
                type: TransactionType::REDEEM,
                points: -$reward->point_required,
                balanceBefore: $balanceBefore,
                reference: $redemption,
                userId: $userId,
                notes: "Penukaran reward: {$reward->reward_name}"
            );

            return $redemption;
        });
    }

    // ═══════════════════════════════════════════════════════════
    // REDEEM POINTS AS PAYMENT (saat checkout POS)
    // ═══════════════════════════════════════════════════════════
    public function redeemPointsAsPayment(
        int $customerId,
        int $pointsToRedeem,
        $reference = null,
        ?int $userId = null,
    ): array {
        return DB::transaction(function () use ($customerId, $pointsToRedeem, $reference, $userId) {
            $account = $this->accountRepo->findOrCreateForCustomer($customerId);
            $config = LoyaltyConfiguration::getInstance();

            if ($pointsToRedeem < $config->minimum_redeem_points) {
                throw new \DomainException(
                    "Minimum redeem: {$config->minimum_redeem_points} poin"
                );
            }

            if ($account->current_balance < $pointsToRedeem) {
                throw new \DomainException(
                    "Poin tidak mencukupi. Tersedia: {$account->current_balance}"
                );
            }

            $balanceBefore = $account->current_balance;
            $this->accountRepo->deductPoints($account->id, $pointsToRedeem);
            $account->increment('lifetime_redeemed', $pointsToRedeem);

            $rupiahValue = $pointsToRedeem * $config->point_value;

            $this->recordTransaction(
                account: $account,
                type: TransactionType::REDEEM,
                points: -$pointsToRedeem,
                balanceBefore: $balanceBefore,
                transactionValue: $rupiahValue,
                reference: $reference,
                userId: $userId,
                notes: 'Pembayaran dengan poin (Rp '.number_format($rupiahValue, 0, ',', '.').')'
            );

            return [
                'points_used' => $pointsToRedeem,
                'rupiah_value' => $rupiahValue,
                'new_balance' => $account->fresh()->current_balance,
            ];
        });
    }

    // ═══════════════════════════════════════════════════════════
    // ADJUSTMENT MANUAL
    // ═══════════════════════════════════════════════════════════
    public function adjustPoints(
        int $customerId,
        AdjustmentType $type,
        int $points,
        string $reason,
        ?string $notes = null,
        ?int $userId = null,
    ): LoyaltyAdjustment {
        return DB::transaction(function () use ($customerId, $type, $points, $reason, $notes, $userId) {
            $account = $this->accountRepo->findOrCreateForCustomer($customerId);

            $adjustment = LoyaltyAdjustment::create([
                'adjustment_number' => $this->generateAdjustmentNumber(),
                'loyalty_account_id' => $account->id,
                'adjustment_type' => $type,
                'points' => $points,
                'reason' => $reason,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            $balanceBefore = $account->current_balance;

            if ($type->isIncrease()) {
                $this->accountRepo->addPoints($account->id, $points);
            } else {
                $this->accountRepo->deductPoints($account->id, $points);
            }

            $this->recordTransaction(
                account: $account,
                type: TransactionType::ADJUSTMENT,
                points: $type->isIncrease() ? $points : -$points,
                balanceBefore: $balanceBefore,
                reference: $adjustment,
                userId: $userId,
                notes: "{$type->label()}: {$reason}"
            );

            return $adjustment;
        });
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    private function recordTransaction(
        LoyaltyAccount $account,
        TransactionType $type,
        int $points,
        int $balanceBefore,
        ?float $transactionValue = null,
        $reference = null,
        ?int $userId = null,
        ?string $notes = null,
    ): LoyaltyTransaction {
        $balanceAfter = $balanceBefore + $points;

        return LoyaltyTransaction::create([
            'reference_number' => $this->generateTransactionNumber($type),
            'transaction_type' => $type,
            'loyalty_account_id' => $account->id,
            'points' => $points,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'transaction_value' => $transactionValue,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'user_id' => $userId,
            'notes' => $notes,
            'transaction_date' => now(),
        ]);
    }

    private function evaluateTierUpgrade(LoyaltyAccount $account): void
    {
        $newTier = $this->tierRepo->determineTier(
            $account->lifetime_spending,
            $account->lifetime_earned
        );

        if ($newTier && $newTier->id !== $account->current_tier_id) {
            $oldTier = $account->tier?->tier_code ?? 'NONE';
            $account->update([
                'current_tier_id' => $newTier->id,
                'tier_evaluation_date' => now()->addYear(),
            ]);

            \Log::info("Customer {$account->customer_id} upgraded from {$oldTier} to {$newTier->tier_code}");
        }
    }

    private function generateTransactionNumber(TransactionType $type): string
    {
        $prefix = match ($type) {
            TransactionType::EARN => 'LYE',
            TransactionType::REDEEM => 'LYR',
            TransactionType::ADJUSTMENT => 'LYA',
            TransactionType::EXPIRE => 'LYX',
            TransactionType::REFUND => 'LYF',
        };
        $date = now()->format('Ymd');
        $last = LoyaltyTransaction::whereDate('created_at', today())
            ->where('reference_number', 'like', "{$prefix}-{$date}-%")
            ->count();

        return sprintf('%s-%s-%04d', $prefix, $date, $last + 1);
    }

    private function generateRedemptionNumber(): string
    {
        $date = now()->format('Ymd');
        $last = LoyaltyRedemption::whereDate('created_at', today())->count();

        return sprintf('LRD-%s-%04d', $date, $last + 1);
    }

    private function generateAdjustmentNumber(): string
    {
        $date = now()->format('Ymd');
        $last = LoyaltyAdjustment::whereDate('created_at', today())->count();

        return sprintf('LAD-%s-%04d', $date, $last + 1);
    }

    private function generateVoucherCode(): string
    {
        return 'VOU-'.strtoupper(Str::random(8));
    }
}
