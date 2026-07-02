<?php

namespace App\Http\Controllers\Api\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\Loyalty\LoyaltyTier;
use Illuminate\Http\Request;

class TierController extends Controller
{
    public function index()
    {
        $tiers = LoyaltyTier::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['success' => true, 'data' => $tiers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tier_code' => 'required|string|unique:loyalty_tiers,tier_code',
            'tier_name' => 'required|string',
            'minimum_spending' => 'numeric|min:0',
            'minimum_points' => 'integer|min:0',
            'point_multiplier' => 'numeric|min:1',
            'discount_percentage' => 'numeric|min:0|max:100',
            'benefits' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $tier = LoyaltyTier::create($validated);

        return response()->json([
            'success' => true,
            'data' => $tier,
            'message' => 'Tier berhasil dibuat',
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $tier = LoyaltyTier::findOrFail($id);

        $validated = $request->validate([
            'tier_code' => 'string|unique:loyalty_tiers,tier_code,'.$id,
            'tier_name' => 'string',
            'minimum_spending' => 'numeric|min:0',
            'minimum_points' => 'integer|min:0',
            'point_multiplier' => 'numeric|min:1',
            'discount_percentage' => 'numeric|min:0|max:100',
            'benefits' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $tier->update($validated);

        return response()->json([
            'success' => true,
            'data' => $tier,
            'message' => 'Tier berhasil diupdate',
        ]);
    }
}
