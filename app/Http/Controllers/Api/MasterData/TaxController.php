<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreTaxRequest;
use App\Http\Requests\MasterData\UpdateTaxRequest;
use App\Http\Resources\MasterData\TaxResource;
use App\Services\MasterData\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct(
        private readonly TaxService $taxService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('master-data.tax.view');

        $taxes = $this->taxService->paginate(
            filters: $request->only(['search', 'is_active']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => TaxResource::collection($taxes->items()),
            'meta' => [
                'current_page' => $taxes->currentPage(),
                'last_page'    => $taxes->lastPage(),
                'per_page'     => $taxes->perPage(),
                'total'        => $taxes->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('master-data.tax.view');

        $tax = $this->taxService->findById($id);
        abort_if(! $tax, 404, 'Tax tidak ditemukan.');

        return response()->json(['data' => new TaxResource($tax)]);
    }

    public function store(StoreTaxRequest $request): JsonResponse
    {
        $tax = $this->taxService->create($request->validated());

        return response()->json([
            'data'    => new TaxResource($tax),
            'message' => 'Tax berhasil ditambahkan.',
        ], 201);
    }

    public function update(UpdateTaxRequest $request, int $id): JsonResponse
    {
        $tax = $this->taxService->findById($id);
        abort_if(! $tax, 404, 'Tax tidak ditemukan.');

        $tax = $this->taxService->update($tax, $request->validated());

        return response()->json([
            'data'    => new TaxResource($tax),
            'message' => 'Tax berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('master-data.tax.manage');

        $tax = $this->taxService->findById($id);
        abort_if(! $tax, 404, 'Tax tidak ditemukan.');

        $this->taxService->delete($tax);

        return response()->json(['message' => 'Tax berhasil dihapus.']);
    }
}
