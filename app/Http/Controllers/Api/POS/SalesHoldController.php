<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreHoldBillRequest;
use App\Http\Resources\POS\SalesHoldResource;
use App\Services\POS\HoldBillService;
use Illuminate\Http\JsonResponse;

class SalesHoldController extends Controller
{
    public function __construct(
        private readonly HoldBillService $holdService,
    ) {}

    public function index(int $sessionId): JsonResponse
    {
        $holds = $this->holdService->findActiveBySession($sessionId);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data'    => SalesHoldResource::collection($holds),
        ]);
    }

    public function store(StoreHoldBillRequest $request): JsonResponse
    {
        $hold = $this->holdService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hold bill created successfully',
            'data'    => new SalesHoldResource($hold),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $hold = $this->holdService->findById($id);

        if (!$hold) {
            return response()->json([
                'success' => false,
                'message' => 'Hold bill not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data'    => new SalesHoldResource($hold),
        ]);
    }

    public function resume(int $id): JsonResponse
    {
        $hold = $this->holdService->findById($id);

        if (!$hold) {
            return response()->json([
                'success' => false,
                'message' => 'Hold bill not found',
            ], 404);
        }

        $result = $this->holdService->resume($hold);

        return response()->json([
            'success' => true,
            'message' => 'Hold bill resumed successfully',
            'data'    => $result,
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $hold = $this->holdService->findById($id);

        if (!$hold) {
            return response()->json([
                'success' => false,
                'message' => 'Hold bill not found',
            ], 404);
        }

        $this->holdService->cancel($hold);

        return response()->json([
            'success' => true,
            'message' => 'Hold bill cancelled successfully',
        ]);
    }
}
