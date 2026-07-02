<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreShiftRequest;
use App\Http\Requests\POS\UpdateShiftRequest;
use App\Http\Resources\POS\ShiftResource;
use App\Services\POS\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(
        private readonly ShiftService $shiftService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $shifts = $this->shiftService->paginate(
            $request->only(['search', 'is_active']),
            $request->input('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => ShiftResource::collection($shifts),
            'meta' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    public function active(): JsonResponse
    {
        $shifts = $this->shiftService->findActive();

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => ShiftResource::collection($shifts),
        ]);
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $shift = $this->shiftService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shift created successfully',
            'data' => new ShiftResource($shift),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $shift = $this->shiftService->findById($id);

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => new ShiftResource($shift),
        ]);
    }

    public function update(UpdateShiftRequest $request, int $id): JsonResponse
    {
        $shift = $this->shiftService->findById($id);

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found',
            ], 404);
        }

        $shift = $this->shiftService->update($shift, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shift updated successfully',
            'data' => new ShiftResource($shift),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $shift = $this->shiftService->findById($id);

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found',
            ], 404);
        }

        $this->shiftService->delete($shift);

        return response()->json([
            'success' => true,
            'message' => 'Shift deleted successfully',
        ]);
    }
}
