<?php

namespace App\Actions\Promotion;

use App\Models\Promotion\Promotion;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class SimulatePromotionAction
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function execute(Promotion $promotion, array $cartItems, ?int $customerId = null): array
    {
        return DB::transaction(function () use ($promotion, $cartItems) {
            $totalDiscount = 0;
            $discountedItems = [];

            foreach ($cartItems as $item) {
                $itemDiscount = $this->calculateItemDiscount($promotion, $item);
                if ($itemDiscount > 0) {
                    $discountedItems[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_amount' => $itemDiscount,
                    ];
                    $totalDiscount += $itemDiscount;
                }
            }

            $this->auditService->activity(
                'SIMULATE_PROMOTION',
                'Promotion',
                "Simulated promotion {$promotion->promotion_no}: Discount {$totalDiscount}"
            );

            return [
                'promotion_id' => $promotion->id,
                'promotion_no' => $promotion->promotion_no,
                'total_discount' => $totalDiscount,
                'discounted_items' => $discountedItems,
            ];
        });
    }

    private function calculateItemDiscount(Promotion $promotion, array $item): float
    {
        $discount = 0;

        foreach ($promotion->rewards as $reward) {
            if ($reward->reward_type === 'PERCENTAGE') {
                $discount += ($item['unit_price'] * $item['quantity'] * $reward->reward_value) / 100;
            } elseif ($reward->reward_type === 'FIXED') {
                $discount += $reward->reward_value * $item['quantity'];
            }
        }

        return min($discount, $item['unit_price'] * $item['quantity']);
    }
}
