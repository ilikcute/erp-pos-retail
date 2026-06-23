<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSupplierRequest;
use App\Http\Requests\MasterData\UpdateSupplierRequest;
use App\Http\Resources\MasterData\SupplierResource;
use App\Services\MasterData\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $supplierService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('master-data.supplier.view');

        $suppliers = $this->supplierService->paginate(
            filters: $request->only(['search', 'is_active', 'city']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => SupplierResource::collection($suppliers->items()),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
                'total'        => $suppliers->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('master-data.supplier.view');

        $supplier = $this->supplierService->findById($id);
        abort_if(! $supplier, 404, 'Supplier tidak ditemukan.');

        return response()->json(['data' => new SupplierResource($supplier)]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->supplierService->create($request->validated());

        return response()->json([
            'data'    => new SupplierResource($supplier),
            'message' => 'Supplier berhasil ditambahkan.',
        ], 201);
    }

    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        $supplier = $this->supplierService->findById($id);
        abort_if(! $supplier, 404, 'Supplier tidak ditemukan.');

        $supplier = $this->supplierService->update($supplier, $request->validated());

        return response()->json([
            'data'    => new SupplierResource($supplier),
            'message' => 'Supplier berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('master-data.supplier.delete');

        $supplier = $this->supplierService->findById($id);
        abort_if(! $supplier, 404, 'Supplier tidak ditemukan.');

        $this->supplierService->delete($supplier);

        return response()->json(['message' => 'Supplier berhasil dihapus.']);
    }
}
