<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreUnitRequest;
use App\Http\Requests\MasterData\UpdateUnitRequest;
use App\Http\Resources\MasterData\UnitResource;
use App\Services\MasterData\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(
        private readonly UnitService $unitService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('master-data.unit.view');

        $units = $this->unitService->paginate(
            filters: $request->only(['search', 'is_active']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => UnitResource::collection($units->items()),
            'meta' => [
                'current_page' => $units->currentPage(),
                'last_page' => $units->lastPage(),
                'per_page' => $units->perPage(),
                'total' => $units->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('master-data.unit.view');

        $unit = $this->unitService->findById($id);
        abort_if(! $unit, 404, 'Unit tidak ditemukan.');

        return response()->json(['data' => new UnitResource($unit)]);
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = $this->unitService->create($request->validated());

        return response()->json([
            'data' => new UnitResource($unit),
            'message' => 'Unit berhasil ditambahkan.',
        ], 201);
    }

    public function update(UpdateUnitRequest $request, int $id): JsonResponse
    {
        $unit = $this->unitService->findById($id);
        abort_if(! $unit, 404, 'Unit tidak ditemukan.');

        $unit = $this->unitService->update($unit, $request->validated());

        return response()->json([
            'data' => new UnitResource($unit),
            'message' => 'Unit berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('master-data.unit.manage');

        $unit = $this->unitService->findById($id);
        abort_if(! $unit, 404, 'Unit tidak ditemukan.');

        $this->unitService->delete($unit);

        return response()->json(['message' => 'Unit berhasil dihapus.']);
    }
}
