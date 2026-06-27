<?php

namespace App\Http\Controllers\Api\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\Loyalty\LoyaltyRewardCatalog;
use Illuminate\Http\Request;

class RewardCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = LoyaltyRewardCatalog::query()
            ->when($request->reward_type, fn($q) => $q->where('reward_type', $request->reward_type))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->is_active))
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reward_code' => 'required|string|unique:loyalty_reward_catalogs,reward_code',
            'reward_name' => 'required|string',
            'reward_type' => 'required|in:VOUCHER,PRODUCT,LUCKY_DRAW',
            'point_required' => 'required|integer|min:1',
            'voucher_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'product_id' => 'nullable|integer|exists:products,id',
            'stock_qty' => 'required|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string',
        ]);

        $reward = LoyaltyRewardCatalog::create($validated);

        return response()->json([
            'success' => true,
            'data' => $reward,
            'message' => 'Reward berhasil dibuat',
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $reward = LoyaltyRewardCatalog::findOrFail($id);

        $validated = $request->validate([
            'reward_code' => 'string|unique:loyalty_reward_catalogs,reward_code,' . $id,
            'reward_name' => 'string',
            'reward_type' => 'in:VOUCHER,PRODUCT,LUCKY_DRAW',
            'point_required' => 'integer|min:1',
            'voucher_amount' => 'nullable|numeric|min:0',
            'stock_qty' => 'integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $reward->update($validated);

        return response()->json([
            'success' => true,
            'data' => $reward,
            'message' => 'Reward berhasil diupdate',
        ]);
    }

    public function destroy(int $id)
    {
        $reward = LoyaltyRewardCatalog::findOrFail($id);
        $reward->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil dihapus',
        ]);
    }
}
