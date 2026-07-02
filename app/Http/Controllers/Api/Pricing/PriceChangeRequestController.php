<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pricing\StorePriceChangeRequest;
use App\Http\Resources\Pricing\PriceChangeRequestResource;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Services\Pricing\PriceChangeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceChangeRequestController extends Controller
{
    public function __construct(
        private readonly PriceChangeRequestRepositoryInterface $priceChangeRequestRepository,
        private readonly PriceChangeRequestService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('pricing.price-change-request.view');

        $requests = $this->priceChangeRequestRepository->paginate(
            filters: $request->only(['status', 'price_list_id']),
            perPage: $request->integer('per_page', 15)
        );

        return response()->json([
            'data' => PriceChangeRequestResource::collection($requests->items()),
            'meta' => ['current_page' => $requests->currentPage(), 'total' => $requests->total()],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('pricing.price-change-request.view');

        $request = $this->priceChangeRequestRepository->findByIdWithRelations($id);
        abort_if(! $request, 404, 'Price change request tidak ditemukan.');

        return response()->json(['data' => new PriceChangeRequestResource($request)]);
    }

    public function store(StorePriceChangeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $priceRequest = $this->service->create($validated);

        return response()->json([
            'data' => new PriceChangeRequestResource($priceRequest),
            'message' => 'Price change request berhasil dibuat.',
        ], 201);
    }

    public function approve(int $id): JsonResponse
    {
        $this->authorize('pricing.price-change-request.approve');

        $priceRequest = $this->priceChangeRequestRepository->findById($id);
        abort_if(! $priceRequest, 404, 'Price change request tidak ditemukan.');

        $priceRequest = $this->service->approve($priceRequest, auth()->id());

        return response()->json([
            'data' => new PriceChangeRequestResource($priceRequest),
            'message' => 'Price change request disetujui.',
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $this->authorize('pricing.price-change-request.approve');

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $priceRequest = $this->priceChangeRequestRepository->findById($id);
        abort_if(! $priceRequest, 404, 'Price change request tidak ditemukan.');

        $priceRequest = $this->service->reject($priceRequest, auth()->id(), $request->reason);

        return response()->json([
            'data' => new PriceChangeRequestResource($priceRequest),
            'message' => 'Price change request ditolak.',
        ]);
    }

    public function apply(int $id): JsonResponse
    {
        $this->authorize('pricing.price-change-request.apply');

        $priceRequest = $this->priceChangeRequestRepository->findByIdWithRelations($id);
        abort_if(! $priceRequest, 404, 'Price change request tidak ditemukan.');

        $priceRequest = $this->service->apply($priceRequest, auth()->id());

        return response()->json([
            'data' => new PriceChangeRequestResource($priceRequest),
            'message' => 'Perubahan harga berhasil diterapkan.',
        ]);
    }
}
