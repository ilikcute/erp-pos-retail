<?php

namespace App\Services\Pricing;

class PricingService
{
    public function calculatePreview($carts, ?int $customerId, float $discount, float $shipping, float $redeemPoints, ?int $voucherId): array
    {
        $items = [];
        $baseSubtotal = 0;
        $promoDiscount = 0;

        foreach ($carts as $cart) {
            $basePrice = $cart->product->sell_price ?? $cart->sell_price;
            $lineBaseTotal = $basePrice * $cart->qty;

            // TODO: Apply promo rules here
            $effectivePrice = $basePrice;
            $lineTotal = $effectivePrice * $cart->qty;
            $promoDiscount += ($basePrice - $effectivePrice) * $cart->qty;

            $items[] = [
                'cart_id' => $cart->id,
                'base_unit_price' => $basePrice,
                'effective_unit_price' => $effectivePrice,
                'line_base_total' => $lineBaseTotal,
                'line_total' => $lineTotal,
                'pricing_rule' => null,
            ];

            $baseSubtotal += $lineBaseTotal;
        }

        $subtotalAfterPromo = $baseSubtotal - $promoDiscount;

        // Manual discount
        $manualDiscount = min($discount, $subtotalAfterPromo);

        // Voucher discount (TODO: implement)
        $voucherDiscount = 0;

        // Loyalty discount (TODO: implement)
        $loyaltyDiscount = 0;

        // Tax (11% PPN)
        $taxableAmount = $subtotalAfterPromo - $manualDiscount - $voucherDiscount - $loyaltyDiscount + $shipping;
        $taxTotal = round($taxableAmount * 0.11);

        $grandTotal = $taxableAmount + $taxTotal;

        return [
            'items' => $items,
            'summary' => [
                'base_subtotal' => $baseSubtotal,
                'promo_discount_total' => $promoDiscount,
                'subtotal_after_promo' => $subtotalAfterPromo,
                'voucher_discount_total' => $voucherDiscount,
                'loyalty_discount_total' => $loyaltyDiscount,
                'manual_discount_total' => $manualDiscount,
                'shipping_cost' => $shipping,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'available_loyalty_points' => 0, // TODO: get from customer
            ],
            'eligible_vouchers' => [],
            'applied_groups' => [],
        ];
    }
}
