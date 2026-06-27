<?php

namespace App\Http\Controllers\Api\Loyalty;

use App\Http\Controllers\Controller;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedemptionController extends Controller
{
    public function __construct(private readonly LoyaltyService $loyaltyService) {}

    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'reward_catalog_id' => 'required|integer|exists:loyalty_reward_catalogs,id',
        ]);

        $redemption = $this->loyaltyService->redeemReward(
            $validated['customer_id'],
            $validated['reward_catalog_id'],
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'data' => $redemption,
            'message' => 'Redeem berhasil',
        ]);
    }
}
