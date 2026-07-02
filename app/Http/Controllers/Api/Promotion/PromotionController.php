<?php

namespace App\Http\Controllers\Api\Promotion;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Promotion\PromotionRepositoryInterface;
use App\Services\Promotion\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function __construct(
        private readonly PromotionService $promoService,
        private readonly PromotionRepositoryInterface $promoRepo,
    ) {}

    public function index(Request $request)
    {
        $this->authorizePromotionView();

        $promotions = $this->promoRepo->getAll($request->only(['status', 'valid_date']));

        return response()->json([
            'success' => true,
            'data' => $promotions,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePromotionManage();

        $validated = $request->validate([
            'promotion_code' => 'required|string|unique:promotions,promotion_code',
            'promotion_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'integer|min:0',
            'stackable' => 'boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'earn_point_allowed' => 'boolean',
            'redeem_point_allowed' => 'boolean',
            'conditions' => 'array',
            'conditions.*.condition_type' => 'required|in:MIN_AMOUNT,MIN_QTY,DAY_OF_WEEK,CUSTOMER_CATEGORY,PRODUCT,CATEGORY',
            'conditions.*.operator' => 'string',
            'conditions.*.condition_value' => 'required',
            'rewards' => 'required|array|min:1',
            'rewards.*.reward_type' => 'required|in:PERCENTAGE,FIXED_AMOUNT,FREE_PRODUCT,SPECIAL_PRICE',
            'rewards.*.reward_value' => 'required|numeric|min:0',
            'rewards.*.max_discount' => 'nullable|numeric|min:0',
            'targets' => 'required|array|min:1',
            'targets.*.target_type' => 'required|in:ALL_PRODUCT,PRODUCT,CATEGORY',
            'targets.*.target_id' => 'nullable|integer',
            'limits' => 'nullable|array',
            'limits.max_usage' => 'integer|min:0',
            'limits.max_usage_per_customer' => 'integer|min:0',
        ]);

        $promotion = $this->promoService->create($validated);

        return response()->json([
            'success' => true,
            'data' => $promotion,
            'message' => 'Promosi berhasil dibuat',
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizePromotionManage();

        $validated = $request->validate([
            'promotion_code' => "required|string|unique:promotions,promotion_code,{$id}",
            'promotion_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'integer|min:0',
            'stackable' => 'boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'earn_point_allowed' => 'boolean',
            'redeem_point_allowed' => 'boolean',
            'conditions' => 'array',
            'conditions.*.condition_type' => 'required|in:MIN_AMOUNT,MIN_QTY,DAY_OF_WEEK,CUSTOMER_CATEGORY,PRODUCT,CATEGORY',
            'conditions.*.operator' => 'string',
            'conditions.*.condition_value' => 'required',
            'rewards' => 'required|array|min:1',
            'rewards.*.reward_type' => 'required|in:PERCENTAGE,FIXED_AMOUNT,FREE_PRODUCT,SPECIAL_PRICE',
            'rewards.*.reward_value' => 'required|numeric|min:0',
            'rewards.*.max_discount' => 'nullable|numeric|min:0',
            'targets' => 'required|array|min:1',
            'targets.*.target_type' => 'required|in:ALL_PRODUCT,PRODUCT,CATEGORY',
            'targets.*.target_id' => 'nullable|integer',
            'limits' => 'nullable|array',
            'limits.max_usage' => 'integer|min:0',
            'limits.max_usage_per_customer' => 'integer|min:0',
        ]);

        $promotion = $this->promoService->update($id, $validated);

        return response()->json([
            'success' => true,
            'data' => $promotion,
            'message' => 'Promosi berhasil diperbarui',
        ]);
    }

    public function show(int $id)
    {
        $this->authorizePromotionView();

        $promotion = $this->promoRepo->findById($id);

        if (! $promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promosi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $promotion,
        ]);
    }

    public function activate(int $id)
    {
        $this->authorizePromotionManage();

        $promotion = $this->promoService->activate($id);

        return response()->json([
            'success' => true,
            'data' => $promotion,
            'message' => 'Promosi diaktifkan',
        ]);
    }

    public function deactivate(int $id)
    {
        $this->authorizePromotionManage();

        $promotion = $this->promoService->deactivate($id);

        return response()->json([
            'success' => true,
            'data' => $promotion,
            'message' => 'Promosi dinonaktifkan',
        ]);
    }

    public function simulate(Request $request)
    {
        // Kasir diperbolehkan melakukan simulasi transaksi di kasir
        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $result = $this->promoService->simulate(
            $validated['customer_id'] ?? null,
            $validated['items']
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    private function authorizePromotionView(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['superadmin', 'admin', 'manager', 'supervisor', 'kasir'])) {
            abort(403, 'Unauthorized');
        }
    }

    private function authorizePromotionManage(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['superadmin', 'admin', 'manager', 'supervisor'])) {
            abort(403, 'Unauthorized');
        }
    }
}
