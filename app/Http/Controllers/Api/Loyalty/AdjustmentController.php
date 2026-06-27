<?php

namespace App\Http\Controllers\Api\Loyalty;

use App\Enums\Loyalty\AdjustmentType;
use App\Http\Controllers\Controller;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdjustmentController extends Controller
{
    public function __construct(private readonly LoyaltyService $loyaltyService) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'adjustment_type' => 'required|in:ADD,DEDUCT',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $adjustment = $this->loyaltyService->adjustPoints(
            $validated['customer_id'],
            AdjustmentType::from($validated['adjustment_type']),
            $validated['points'],
            $validated['reason'],
            $validated['notes'] ?? null,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'data' => $adjustment,
            'message' => 'Adjustment berhasil',
        ]);
    }
}
