<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryPlanogram;
use App\Models\Inventory\InventoryLocation;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlanogramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $planograms = InventoryPlanogram::with(['variant.product', 'location'])->get();
        return response()->json([
            'success' => true,
            'data' => $planograms,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'position_code' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $planogram = InventoryPlanogram::create($validated);

        return response()->json([
            'success' => true,
            'data' => $planogram->load(['variant.product', 'location']),
            'message' => 'Planogram rak berhasil ditambahkan.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $planogram = InventoryPlanogram::findOrFail($id);

        $validated = $request->validate([
            'product_variant_id' => 'exists:product_variants,id',
            'location_id' => 'exists:inventory_locations,id',
            'position_code' => 'string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $planogram->update($validated);

        return response()->json([
            'success' => true,
            'data' => $planogram->load(['variant.product', 'location']),
            'message' => 'Planogram rak berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $planogram = InventoryPlanogram::findOrFail($id);
        $planogram->delete();

        return response()->json([
            'success' => true,
            'message' => 'Planogram rak berhasil dihapus.',
        ]);
    }
}
