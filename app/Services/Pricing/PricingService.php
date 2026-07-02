<?php

namespace App\Services\Pricing;

use App\Models\Loyalty\LoyaltyConfiguration;  // ✅ FIXED: Import PromotionService
use App\Repositories\Contracts\Loyalty\AccountRepositoryInterface;
use App\Services\Promotion\PromotionService;
use Illuminate\Support\Collection;

class PricingService
{
    public function __construct(
        private readonly PromotionService $promotionService,
    ) {}

    /**
     * Hitung preview harga untuk cart + customer
     * Termasuk: harga dasar, promo otomatis, voucher, loyalty, pajak
     *
     * @param  Collection  $carts  Cart items dari database
     * @param  int|null  $customerId  ID customer (untuk loyalty & voucher)
     * @param  float  $manualDiscount  Diskon manual dari kasir
     * @param  float  $shippingCost  Ongkos kirim
     * @param  float  $redeemPoints  Poin yang ditukar
     * @param  int|null  $voucherId  ID voucher yang dipilih
     * @return array Struktur preview lengkap
     */
    public function calculatePreview(
        $carts,
        ?int $customerId = null,
        float $manualDiscount = 0,
        float $shippingCost = 0,
        float $redeemPoints = 0,
        ?int $voucherId = null
    ): array {
        // ═══════════════════════════════════════════════════════════
        // 1. HITUNG HARGA DASAR PER ITEM
        // ═══════════════════════════════════════════════════════════
        $items = [];
        $baseSubtotal = 0;

        foreach ($carts as $cart) {
            // ✅ SAFE: Ambil harga dari priceListItems dengan fallback
            $baseUnitPrice = $this->getBaseUnitPrice($cart);
            $lineBaseTotal = $baseUnitPrice * $cart->qty;

            $items[$cart->id] = [
                'cart_id' => $cart->id,
                'product_id' => $cart->product_id,
                'category_id' => $cart->product->category_id ?? null,
                'qty' => $cart->qty,
                'base_unit_price' => $baseUnitPrice,
                'effective_unit_price' => $baseUnitPrice, // Akan diupdate jika ada promo
                'line_base_total' => $lineBaseTotal,
                'line_total' => $lineBaseTotal, // Akan diupdate jika ada promo
                'pricing_rule' => null,
                'discount_amount' => 0,
            ];

            $baseSubtotal += $lineBaseTotal;
        }

        // ═══════════════════════════════════════════════════════════
        // 2. TERAPKAN PROMOSI OTOMATIS
        // ═══════════════════════════════════════════════════════════
        $promotionResult = $this->applyPromotions($items, $customerId);
        $promoDiscountTotal = $promotionResult['total_discount'];
        $appliedPromotions = $promotionResult['applied_promotions'];

        // Update items dengan harga setelah promo
        foreach ($promotionResult['item_discounts'] as $cartId => $discount) {
            if (! isset($items[$cartId])) {
                continue;
            }

            $items[$cartId]['discount_amount'] = $discount;
            $items[$cartId]['line_total'] = $items[$cartId]['line_base_total'] - $discount;
            $items[$cartId]['effective_unit_price'] = $items[$cartId]['qty'] > 0
                ? $items[$cartId]['line_total'] / $items[$cartId]['qty']
                : 0;

            // Attach promo info ke item (ambil promo pertama yang apply)
            $appliedPromo = collect($appliedPromotions)
                ->first(fn ($p) => in_array($cartId, $p['affected_cart_ids'] ?? []));

            if ($appliedPromo) {
                $items[$cartId]['pricing_rule'] = [
                    'id' => $appliedPromo['promotion_id'],
                    'name' => $appliedPromo['promotion_name'],
                    'code' => $appliedPromo['promotion_code'],
                    'type' => 'PROMOTION',
                    'discount_amount' => $discount,
                ];
            }
        }

        // ═══════════════════════════════════════════════════════════
        // 3. HITUNG SUBTOTAL SETELAH PROMO
        // ═══════════════════════════════════════════════════════════
        $subtotalAfterPromo = collect($items)->sum('line_total');

        // ═══════════════════════════════════════════════════════════
        // 4. VOUCHER & LOYALTY DISCOUNT
        // ═══════════════════════════════════════════════════════════
        $voucherData = $this->calculateVoucherDiscount($voucherId, $subtotalAfterPromo);
        $voucherDiscount = $voucherData['discount'];
        $eligibleVouchers = $voucherData['eligible'];

        $loyaltyData = $this->calculateLoyaltyDiscount($customerId, $redeemPoints, $subtotalAfterPromo);
        $loyaltyDiscount = $loyaltyData['discount'];
        $availablePoints = $loyaltyData['available'];

        // ═══════════════════════════════════════════════════════════
        // 5. DISKON MANUAL
        // ═══════════════════════════════════════════════════════════
        $remainingAfterDiscounts = $subtotalAfterPromo - $voucherDiscount - $loyaltyDiscount;
        $manualDiscountApplied = min($manualDiscount, max(0, $remainingAfterDiscounts));

        // ═══════════════════════════════════════════════════════════
        // 6. PAJAK (Berdasarkan Setting Pajak Produk)
        // ═══════════════════════════════════════════════════════════
        $totalDiscounts = $voucherDiscount + $loyaltyDiscount + $manualDiscountApplied;
        $discountRatio = $subtotalAfterPromo > 0 ? ($totalDiscounts / $subtotalAfterPromo) : 0;

        $taxTotal = 0;
        foreach ($carts as $cart) {
            $taxRate = 0;
            if ($cart->product) {
                $tax = $cart->product->tax;
                if ($tax && $tax->is_active) {
                    $taxRate = (float) $tax->tax_rate;
                }
            }

            // Find corresponding item in $items to get line_total (which has promo discount applied)
            $itemLineTotal = $items[$cart->id]['line_total'] ?? 0;
            $itemNetTotal = max(0, $itemLineTotal * (1 - $discountRatio));

            // Calculate tax for this item
            $itemTax = $itemNetTotal * ($taxRate / 100);
            $taxTotal += $itemTax;

            if (isset($items[$cart->id])) {
                $items[$cart->id]['tax_amount'] = (float) round($itemTax, 2);
            }
        }

        $taxTotal = (float) round($taxTotal, 2);

        $taxableAmount = max(
            0,
            $subtotalAfterPromo
                - $voucherDiscount
                - $loyaltyDiscount
                - $manualDiscountApplied
                + $shippingCost
        );

        // ═══════════════════════════════════════════════════════════
        // 7. GRAND TOTAL
        // ═══════════════════════════════════════════════════════════
        $grandTotal = $taxableAmount + $taxTotal;

        // ═══════════════════════════════════════════════════════════
        // 8. RETURN HASIL
        // ═══════════════════════════════════════════════════════════
        return [
            'items' => array_values($items),
            'summary' => [
                'base_subtotal' => (float) $baseSubtotal,
                'promo_discount_total' => (float) $promoDiscountTotal,
                'subtotal_after_promo' => (float) $subtotalAfterPromo,
                'voucher_discount_total' => (float) $voucherDiscount,
                'loyalty_discount_total' => (float) $loyaltyDiscount,
                'manual_discount_total' => (float) $manualDiscountApplied,
                'shipping_cost' => (float) $shippingCost,
                'tax_total' => (float) $taxTotal,
                'grand_total' => (float) $grandTotal,
                'available_loyalty_points' => (int) $availablePoints,
            ],
            'applied_promotions' => $appliedPromotions,
            'eligible_vouchers' => $eligibleVouchers,
            'applied_groups' => [], // Reserved for future use
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    /**
     * ✅ NEW: Terapkan promosi ke cart items
     */
    private function applyPromotions(array $items, ?int $customerId): array
    {
        if (empty($items)) {
            return [
                'total_discount' => 0,
                'applied_promotions' => [],
                'item_discounts' => [],
            ];
        }

        // Format items untuk PromotionService
        $cartItemsForPromo = collect($items)->map(fn ($item) => [
            'id' => $item['cart_id'],
            'product_id' => $item['product_id'],
            'category_id' => $item['category_id'],
            'qty' => $item['qty'],
            'unit_price' => $item['base_unit_price'],
        ])->toArray();

        // Simulasi promosi
        try {
            $result = $this->promotionService->simulate($customerId, $cartItemsForPromo);
        } catch (\Throwable $e) {
            // ✅ SAFE: Jika promotion service error, lanjut tanpa promo
            \Log::warning('Promotion simulation failed: '.$e->getMessage());

            return [
                'total_discount' => 0,
                'applied_promotions' => [],
                'item_discounts' => [],
            ];
        }

        // Distribusi diskon ke tiap cart item
        $itemDiscounts = [];
        foreach ($result['applied_promotions'] as $promo) {
            // Track affected cart IDs untuk referensi di item
            $affectedCartIds = [];

            foreach ($promo['item_discounts'] as $itemDiscount) {
                $cartId = $itemDiscount['item_id'] ?? null;
                if ($cartId) {
                    $itemDiscounts[$cartId] = ($itemDiscounts[$cartId] ?? 0) + $itemDiscount['discount'];
                    $affectedCartIds[] = $cartId;
                }
            }

            // ✅ NEW: Tambahkan affected_cart_ids ke promo info
            $promo['affected_cart_ids'] = $affectedCartIds;
        }

        return [
            'total_discount' => (float) $result['total_discount'],
            'applied_promotions' => $result['applied_promotions'],
            'item_discounts' => $itemDiscounts,
        ];
    }

    /**
     * ✅ NEW: Ambil base unit price dari cart item
     */
    private function getBaseUnitPrice($cart): float
    {
        // Priority 1: priceListItems (jika di-eager load)
        if ($cart->product && $cart->product->priceListItems && $cart->product->priceListItems->isNotEmpty()) {
            $priceItem = $cart->product->priceListItems
                ->sortBy('min_qty')
                ->first();
            if ($priceItem && $priceItem->price > 0) {
                return (float) $priceItem->price;
            }
        }

        // Priority 2: sell_price dari cart
        if ($cart->sell_price > 0) {
            return (float) $cart->sell_price;
        }

        // Priority 3: sell_price dari product (fallback)
        if ($cart->product && isset($cart->product->sell_price)) {
            return (float) $cart->product->sell_price;
        }

        return 0.0;
    }

    /**
     * ✅ NEW: Hitung diskon voucher
     */
    private function calculateVoucherDiscount(?int $voucherId, float $subtotal): array
    {
        if (! $voucherId) {
            return ['discount' => 0, 'eligible' => []];
        }

        // TODO: Implement voucher logic
        // Contoh implementasi:
        // $voucher = CustomerVoucher::with('voucher')->find($voucherId);
        // if ($voucher && $voucher->isEligible($subtotal)) {
        //     return ['discount' => $voucher->calculateDiscount($subtotal), 'eligible' => []];
        // }

        return ['discount' => 0, 'eligible' => []];
    }

    /**
     * ✅ NEW: Hitung diskon loyalty points
     */
    private function calculateLoyaltyDiscount(?int $customerId, float $redeemPoints, float $subtotal): array
    {
        if (! $customerId || $redeemPoints <= 0) {
            return ['discount' => 0, 'available' => 0];
        }

        try {
            $accountRepo = app(AccountRepositoryInterface::class);
            $account = $accountRepo->findByCustomerId($customerId);

            if (! $account) {
                return ['discount' => 0, 'available' => 0];
            }

            $config = LoyaltyConfiguration::getInstance();
            $pointValue = $config->point_value ?? 100; // Rp per poin

            // Validasi poin tidak melebihi saldo
            $validPoints = min($redeemPoints, $account->current_balance);

            // Validasi nilai diskon tidak melebihi subtotal
            $discountValue = $validPoints * $pointValue;
            $discount = min($discountValue, $subtotal);

            return [
                'discount' => (float) $discount,
                'available' => (int) $account->current_balance,
            ];
        } catch (\Throwable $e) {
            \Log::warning('Loyalty calculation failed: '.$e->getMessage());

            return ['discount' => 0, 'available' => 0];
        }
    }
}
