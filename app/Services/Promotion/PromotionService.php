<?php

namespace App\Services\Promotion;

use App\Enums\Promotion\PromotionStatus;
use App\Models\Promotion\Promotion;
use App\Models\Promotion\PromotionCondition;
use App\Models\Promotion\PromotionReward;
use App\Models\Promotion\PromotionTarget;
use App\Models\Promotion\PromotionUsageLog;
use App\Repositories\Contracts\Promotion\PromotionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function __construct(
        private readonly PromotionRepositoryInterface $promoRepo,
        private readonly PromotionEvaluator $evaluator,
        private readonly PromotionApplier $applier,
    ) {}

    /**
     * Simulasi promosi untuk cart + customer
     */
    public function simulate(int $customerId = null, array $items): array
    {
        $activePromotions = $this->promoRepo->findActivePromotions();
        $settings = \App\Models\Promotion\PromotionSetting::getInstance();

        $appliedPromotions = [];
        $totalDiscount = 0;
        $subtotal = collect($items)->sum(fn($i) => ($i['unit_price'] ?? 0) * ($i['qty'] ?? 0));

        // Sort by priority
        $sortedPromotions = $activePromotions->sortByDesc('priority');

        foreach ($sortedPromotions as $promotion) {
            // Cek stackable
            if (!empty($appliedPromotions) && !$promotion->stackable && !$settings->allow_stacking) {
                continue;
            }

            // Cek max stacking
            if (count($appliedPromotions) >= $settings->max_stacking_promotions) {
                break;
            }

            // Evaluasi promo
            $evaluation = $this->evaluator->evaluate($promotion, $items, $customerId);

            if ($evaluation['eligible']) {
                // Hitung diskon
                $discountResult = $this->applier->calculateDiscount($promotion, $evaluation['applicable_items']);

                $appliedPromotions[] = [
                    'promotion_id' => $promotion->id,
                    'promotion_code' => $promotion->promotion_code,
                    'promotion_name' => $promotion->promotion_name,
                    'discount_amount' => $discountResult['total_discount'],
                    'item_discounts' => $discountResult['item_discounts'],
                ];

                $totalDiscount += $discountResult['total_discount'];
            }
        }

        return [
            'subtotal' => $subtotal,
            'applied_promotions' => $appliedPromotions,
            'total_discount' => $totalDiscount,
            'grand_total' => max(0, $subtotal - $totalDiscount),
        ];
    }

    /**
     * Log penggunaan promo saat checkout
     */
    public function logUsage(int $promotionId, ?int $customerId, ?int $transactionId, float $discountAmount): void
    {
        DB::transaction(function () use ($promotionId, $customerId, $transactionId, $discountAmount) {
            PromotionUsageLog::create([
                'promotion_id' => $promotionId,
                'customer_id' => $customerId,
                'transaction_id' => $transactionId,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);

            // Increment current_usage
            Promotion::where('id', $promotionId)->increment('current_usage');
        });
    }

    /**
     * Create promotion dengan semua relasi
     */
    public function create(array $data): Promotion
    {
        return DB::transaction(function () use ($data) {
            $promotion = Promotion::create([
                'promotion_code' => $data['promotion_code'],
                'promotion_name' => $data['promotion_name'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 0,
                'stackable' => $data['stackable'] ?? false,
                'valid_from' => $data['valid_from'],
                'valid_until' => $data['valid_until'],
                'status' => PromotionStatus::DRAFT,
                'earn_point_allowed' => $data['earn_point_allowed'] ?? true,
                'redeem_point_allowed' => $data['redeem_point_allowed'] ?? true,
                'limits' => $data['limits'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create conditions
            if (!empty($data['conditions'])) {
                foreach ($data['conditions'] as $condition) {
                    PromotionCondition::create([
                        'promotion_id' => $promotion->id,
                        'condition_type' => $condition['condition_type'],
                        'operator' => $condition['operator'] ?? '>=',
                        'condition_value' => is_array($condition['condition_value'])
                            ? json_encode($condition['condition_value'])
                            : $condition['condition_value'],
                    ]);
                }
            }

            // Create rewards
            if (!empty($data['rewards'])) {
                foreach ($data['rewards'] as $reward) {
                    PromotionReward::create([
                        'promotion_id' => $promotion->id,
                        'reward_type' => $reward['reward_type'],
                        'reward_value' => $reward['reward_value'],
                        'max_discount' => $reward['max_discount'] ?? null,
                        'free_product_id' => $reward['free_product_id'] ?? null,
                        'free_product_qty' => $reward['free_product_qty'] ?? 1,
                    ]);
                }
            }

            // Create targets
            if (!empty($data['targets'])) {
                foreach ($data['targets'] as $target) {
                    PromotionTarget::create([
                        'promotion_id' => $promotion->id,
                        'target_type' => $target['target_type'],
                        'target_id' => $target['target_id'] ?? null,
                    ]);
                }
            }

            return $promotion->load(['conditions', 'rewards', 'targets']);
        });
    }

    /**
     * Activate promotion
     */
    public function activate(int $id): Promotion
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update([
            'status' => PromotionStatus::ACTIVE,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $promotion;
    }

    /**
     * Deactivate promotion
     */
    public function deactivate(int $id): Promotion
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update(['status' => PromotionStatus::INACTIVE]);

        return $promotion;
    }
}
