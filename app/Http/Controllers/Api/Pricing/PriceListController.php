<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pricing\StorePriceListRequest;
use App\Http\Requests\Pricing\UpdatePriceListRequest;
use App\Http\Resources\Pricing\PriceListResource;
use App\Http\Resources\Pricing\PriceListItemResource;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceHistoryRepositoryInterface;
use App\Services\Pricing\PriceResolverService;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    public function __construct(
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly PriceListItemRepositoryInterface $priceListItemRepository,
        private readonly PriceHistoryRepositoryInterface $priceHistoryRepository,
        private readonly PriceResolverService $priceResolver,
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('pricing.price-list.view');

        $priceLists = $this->priceListRepository->paginate(
            filters: $request->only(['search', 'type', 'is_active']),
            perPage: $request->integer('per_page', 15)
        );

        return response()->json([
            'data' => PriceListResource::collection($priceLists->items()),
            'meta' => [
                'current_page' => $priceLists->currentPage(),
                'last_page'    => $priceLists->lastPage(),
                'per_page'     => $priceLists->perPage(),
                'total'        => $priceLists->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('pricing.price-list.view');

        $priceList = $this->priceListRepository->findById($id);
        abort_if(! $priceList, 404, 'Price list tidak ditemukan.');

        return response()->json(['data' => new PriceListResource($priceList)]);
    }

    public function store(StorePriceListRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $categoryIds = $validated['customer_category_ids'] ?? [];
        unset($validated['customer_category_ids']);
        
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $priceList = $this->priceListRepository->create($validated);

        if ($categoryIds) {
            $priceList->customerCategories()->sync($categoryIds);
        }

        $this->auditService->log('Pricing', 'CREATE_PRICE_LIST', 'price_lists', $priceList->id);

        return response()->json([
            'data'    => new PriceListResource($priceList->load('customerCategories')),
            'message' => 'Price list berhasil dibuat.',
        ], 201);
    }

    public function update(UpdatePriceListRequest $request, int $id): JsonResponse
    {
        $priceList = $this->priceListRepository->findById($id);
        abort_if(! $priceList, 404, 'Price list tidak ditemukan.');

        $validated = $request->validated();
        $categoryIds = $validated['customer_category_ids'] ?? null;
        unset($validated['customer_category_ids']);
        
        $validated['updated_by'] = auth()->id();

        $priceList = $this->priceListRepository->update($priceList, $validated);

        if ($categoryIds !== null) {
            $priceList->customerCategories()->sync($categoryIds);
        }

        return response()->json([
            'data'    => new PriceListResource($priceList->fresh('customerCategories')),
            'message' => 'Price list berhasil diperbarui.',
        ]);
    }

    // ── Price List Items ──────────────────────────────────────────────

    public function indexItems(int $id, Request $request): JsonResponse
    {
        $this->authorize('pricing.price-list.view');

        $priceList = $this->priceListRepository->findById($id);
        abort_if(! $priceList, 404, 'Price list tidak ditemukan.');

        $items = $this->priceListItemRepository->paginateByPriceList(
            priceListId: $id,
            filters: $request->only(['search']),
            perPage: $request->integer('per_page', 50)
        );

        return response()->json([
            'data' => PriceListItemResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function storeItem(Request $request, int $id): JsonResponse
    {
        $this->authorize('pricing.price-list.manage');

        $priceList = $this->priceListRepository->findById($id);
        abort_if(! $priceList, 404, 'Price list tidak ditemukan.');

        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'unit_id'            => ['required', 'integer', 'exists:units,id'],
            'price'              => ['required', 'numeric', 'min:0'],
            'min_qty'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['price_list_id'] = $id;
        $validated['created_by']    = auth()->id();
        $validated['updated_by']    = auth()->id();

        $item = $this->priceListItemRepository->createOrUpdate(
            [
                'price_list_id'      => $id,
                'product_variant_id' => $validated['product_variant_id'],
                'unit_id'            => $validated['unit_id'],
                'min_qty'            => $validated['min_qty'] ?? 1.0,
            ],
            $validated
        );

        return response()->json([
            'data'    => new PriceListItemResource($item->load('variant.product')),
            'message' => 'Item harga berhasil disimpan.',
        ], 201);
    }

    // ── Price Resolver ────────────────────────────────────────────────

    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'customer_id'        => ['nullable', 'integer', 'exists:customers,id'],
            'unit_id'            => ['nullable', 'integer', 'exists:units,id'],
            'qty'                => ['nullable', 'numeric', 'min:0.001'],
        ]);

        $price = $this->priceResolver->resolve(
            variantId: $validated['product_variant_id'],
            customerId: $validated['customer_id'] ?? null,
            unitId: $validated['unit_id'] ?? null,
            qty: $validated['qty'] ?? 1.0,
        );

        return response()->json(['data' => $price]);
    }

    // ── Price History ─────────────────────────────────────────────────

    public function priceHistory(Request $request, int $variantId): JsonResponse
    {
        $this->authorize('pricing.price-list.view');

        $histories = $this->priceHistoryRepository->paginateByVariant(
            variantId: $variantId,
            perPage: $request->integer('per_page', 25)
        );

        return response()->json([
            'data' => $histories->items(),
            'meta' => [
                'current_page' => $histories->currentPage(),
                'total'        => $histories->total(),
            ],
        ]);
    }
}
