<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('pricing.price-list.view');

        $loyaltyAccounts = collect([
            [
                'id' => 1,
                'customer' => ['customer_name' => 'Andi Susanto'],
                'account_number' => 'LY-081234567',
                'current_points' => 1250,
                'tier' => ['tier_name' => 'Gold'],
                'is_active' => true,
            ],
            [
                'id' => 2,
                'customer' => ['customer_name' => 'Dewi Lestari'],
                'account_number' => 'LY-087888123',
                'current_points' => 320,
                'tier' => ['tier_name' => 'Regular'],
                'is_active' => true,
            ],
            [
                'id' => 3,
                'customer' => ['customer_name' => 'Rian Hidayat'],
                'account_number' => 'LY-085299887',
                'current_points' => 8450,
                'tier' => ['tier_name' => 'Platinum'],
                'is_active' => true,
            ]
        ]);

        $loyaltyTransactions = collect([
            [
                'id' => 1,
                'loyalty_account' => ['customer' => ['customer_name' => 'Andi Susanto']],
                'reason' => 'Pembelian di POS #POS-20260624-0001',
                'transaction_date' => '2026-06-24',
                'transaction_type' => 'EARN',
                'points' => 120,
            ],
            [
                'id' => 2,
                'loyalty_account' => ['customer' => ['customer_name' => 'Dewi Lestari']],
                'reason' => 'Penukaran poin Voucher Belanja Rp 10.000',
                'transaction_date' => '2026-06-23',
                'transaction_type' => 'REDEEM',
                'points' => 100,
            ],
            [
                'id' => 3,
                'loyalty_account' => ['customer' => ['customer_name' => 'Rian Hidayat']],
                'reason' => 'Bonus promo ulang tahun reseller',
                'transaction_date' => '2026-06-22',
                'transaction_type' => 'EARN',
                'points' => 500,
            ]
        ]);

        $membershipTiers = collect([
            [
                'id' => 1,
                'tier_name' => 'Regular',
                'minimum_points' => 0,
                'point_multiplier' => 1.0,
                'discount_percentage' => 0,
            ],
            [
                'id' => 2,
                'tier_name' => 'Gold',
                'minimum_points' => 1000,
                'point_multiplier' => 1.2,
                'discount_percentage' => 2,
            ],
            [
                'id' => 3,
                'tier_name' => 'Platinum',
                'minimum_points' => 5000,
                'point_multiplier' => 1.5,
                'discount_percentage' => 5,
            ]
        ]);

        return Inertia::render('Loyalty/Index', [
            'loyaltyAccounts' => $loyaltyAccounts,
            'loyaltyTransactions' => $loyaltyTransactions,
            'membershipTiers' => $membershipTiers,
        ]);
    }
}
