<?php

namespace App\Services\Promotion;

use App\Enums\Promotion\RewardType;
use App\Models\Promotion\Promotion;
use Illuminate\Support\Collection;

class PromotionApplier
{
    /**
     * Hitung diskon dari reward promotion
     */
    public function calculateDiscount(Promotion $promotion, array $applicableItems): array
    {
        $totalDiscount = 0;
        $itemDiscounts = [];

        foreach ($promotion->rewards as $reward) {
            $discountResult = $this->applyReward($reward, $applicableItems);
            $totalDiscount += $discountResult['total'];
            $itemDiscounts = array_merge($itemDiscounts, $discountResult['items']);
        }

        return [
            'promotion_id' => $promotion->id,
            'promotion_code' => $promotion->promotion_code,
            'promotion_name' => $promotion->promotion_name,
            'total_discount' => $totalDiscount,
            'item_discounts' => $itemDiscounts,
        ];
    }

    private function applyReward($reward, array $applicableItems): array
    {
        return match ($reward->reward_type) {
            RewardType::PERCENTAGE => $this->applyPercentage($reward, $applicableItems),
            RewardType::FIXED_AMOUNT => $this->applyFixedAmount($reward, $applicableItems),
            RewardType::SPECIAL_PRICE => $this->applySpecialPrice($reward, $applicableItems),
            RewardType::FREE_PRODUCT => $this->applyFreeProduct($reward, $applicableItems),
            default => ['total' => 0, 'items' => []],
        };
    }

    private function applyPercentage($reward, array $applicableItems): array
    {
        $totalDiscount = 0;
        $itemDiscounts = [];

        foreach ($applicableItems as $item) {
            $itemTotal = $item['unit_price'] * $item['qty'];
            $discount = $itemTotal * ($reward->reward_value / 100);

            // Apply max discount cap
            if ($reward->max_discount && $discount > $reward->max_discount) {
                $discount = $reward->max_discount;
            }

            $totalDiscount += $discount;
            $itemDiscounts[] = [
                'item_id' => $item['id'] ?? null,
                'product_id' => $item['product_id'],
                'discount' => $discount,
            ];
        }

        return ['total' => $totalDiscount, 'items' => $itemDiscounts];
    }

    private function applyFixedAmount($reward, array $applicableItems): array
    {
        // Fixed amount diterapkan ke total, bukan per item
        $totalApplicable = collect($applicableItems)->sum(fn($i) => $i['unit_price'] * $i['qty']);
        $discount = min($reward->reward_value, $totalApplicable);

        return [
            'total' => $discount,
            'items' => [
                [
                    'item_id' => null,
                    'product_id' => null,
                    'discount' => $discount,
                    'note' => 'Fixed amount discount',
                ]
            ],
        ];
    }

    private function applySpecialPrice($reward, array $applicableItems): array
    {
        $totalDiscount = 0;
        $itemDiscounts = [];

        foreach ($applicableItems as $item) {
            $originalPrice = $item['unit_price'];
            $specialPrice = $reward->reward_value;

            if ($originalPrice > $specialPrice) {
                $discount = ($originalPrice - $specialPrice) * $item['qty'];
                $totalDiscount += $discount;
                $itemDiscounts[] = [
                    'item_id' => $item['id'] ?? null,
                    'product_id' => $item['product_id'],
                    'discount' => $discount,
                    'new_price' => $specialPrice,
                ];
            }
        }

        return ['total' => $totalDiscount, 'items' => $itemDiscounts];
    }

    private function applyFreeProduct($reward, array $applicableItems): array
    {
        // Free product logic: tambahkan item gratis
        // Implementasi tergantung business logic
        return [
            'total' => 0, // Akan ditangani di level cart
            'items' => [
                [
                    'free_product_id' => $reward->free_product_id,
                    'free_qty' => $reward->free_product_qty,
                ]
            ],
        ];
    }
}
