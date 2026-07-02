<?php

namespace App\Services\Promotion;

use App\Enums\Promotion\ConditionType;
use App\Enums\Promotion\TargetType;
use App\Models\MasterData\Customer;
use App\Models\Promotion\Promotion;

class PromotionEvaluator
{
    /**
     * Evaluasi apakah promo bisa diterapkan ke cart + customer
     */
    public function evaluate(Promotion $promotion, array $cartItems, ?int $customerId = null): array
    {
        $result = [
            'eligible' => true,
            'reason' => null,
            'applicable_items' => [],
        ];

        // 1. Cek status & waktu
        if (! $promotion->isCurrentlyActive()) {
            return [
                'eligible' => false,
                'reason' => 'Promosi tidak aktif atau sudah kadaluarsa',
            ];
        }

        // 2. Cek max usage
        if ($promotion->hasReachedMaxUsage()) {
            return [
                'eligible' => false,
                'reason' => 'Promosi sudah mencapai batas penggunaan',
            ];
        }

        // 3. Cek customer max usage
        if ($customerId && $promotion->hasCustomerReachedMaxUsage($customerId)) {
            return [
                'eligible' => false,
                'reason' => 'Anda sudah mencapai batas penggunaan promosi ini',
            ];
        }

        // 4. Evaluasi conditions
        foreach ($promotion->conditions as $condition) {
            $conditionResult = $this->evaluateCondition($condition, $cartItems, $customerId);
            if (! $conditionResult['pass']) {
                return [
                    'eligible' => false,
                    'reason' => $conditionResult['reason'],
                ];
            }
        }

        // 5. Tentukan items yang applicable
        $result['applicable_items'] = $this->getApplicableItems($promotion, $cartItems);

        if (empty($result['applicable_items'])) {
            return [
                'eligible' => false,
                'reason' => 'Tidak ada produk yang memenuhi syarat promosi',
            ];
        }

        return $result;
    }

    private function evaluateCondition($condition, array $cartItems, ?int $customerId): array
    {
        return match ($condition->condition_type) {
            ConditionType::MIN_AMOUNT => $this->evaluateMinAmount($condition, $cartItems),
            ConditionType::MIN_QTY => $this->evaluateMinQty($condition, $cartItems),
            ConditionType::DAY_OF_WEEK => $this->evaluateDayOfWeek($condition),
            ConditionType::CUSTOMER_CATEGORY => $this->evaluateCustomerCategory($condition, $customerId),
            default => ['pass' => true, 'reason' => null],
        };
    }

    private function evaluateMinAmount($condition, array $cartItems): array
    {
        $total = collect($cartItems)->sum(fn ($item) => ($item['unit_price'] ?? 0) * ($item['qty'] ?? 0));
        $minAmount = (float) $condition->condition_value;

        if ($total < $minAmount) {
            return [
                'pass' => false,
                'reason' => 'Minimum belanja Rp '.number_format($minAmount, 0, ',', '.').' belum terpenuhi',
            ];
        }

        return ['pass' => true, 'reason' => null];
    }

    private function evaluateMinQty($condition, array $cartItems): array
    {
        $totalQty = collect($cartItems)->sum('qty');
        $minQty = (int) $condition->condition_value;

        if ($totalQty < $minQty) {
            return [
                'pass' => false,
                'reason' => "Minimum quantity {$minQty} belum terpenuhi",
            ];
        }

        return ['pass' => true, 'reason' => null];
    }

    private function evaluateDayOfWeek($condition): array
    {
        $allowedDays = $condition->getDecodedValue();
        if (! is_array($allowedDays)) {
            $allowedDays = [$allowedDays];
        }

        $currentDay = now()->dayOfWeek; // 0 = Sunday, 6 = Saturday

        if (! in_array($currentDay, $allowedDays)) {
            return [
                'pass' => false,
                'reason' => 'Promosi hanya berlaku pada hari tertentu',
            ];
        }

        return ['pass' => true, 'reason' => null];
    }

    private function evaluateCustomerCategory($condition, ?int $customerId): array
    {
        if (! $customerId) {
            return [
                'pass' => false,
                'reason' => 'Promosi hanya untuk kategori customer tertentu',
            ];
        }

        $allowedCategories = $condition->getDecodedValue();
        if (! is_array($allowedCategories)) {
            $allowedCategories = [$allowedCategories];
        }

        $customer = Customer::find($customerId);
        if (! $customer || ! in_array($customer->customer_category_id, $allowedCategories)) {
            return [
                'pass' => false,
                'reason' => 'Promosi tidak berlaku untuk kategori customer Anda',
            ];
        }

        return ['pass' => true, 'reason' => null];
    }

    private function getApplicableItems(Promotion $promotion, array $cartItems): array
    {
        $targets = $promotion->targets;

        // Jika target ALL_PRODUCT, semua items applicable
        if ($targets->contains('target_type', TargetType::ALL_PRODUCT)) {
            return $cartItems;
        }

        // Filter berdasarkan target
        return collect($cartItems)->filter(function ($item) use ($targets) {
            foreach ($targets as $target) {
                if ($target->target_type === TargetType::PRODUCT && $target->target_id == $item['product_id']) {
                    return true;
                }
                if ($target->target_type === TargetType::CATEGORY && $target->target_id == $item['category_id']) {
                    return true;
                }
            }

            return false;
        })->toArray();
    }
}
