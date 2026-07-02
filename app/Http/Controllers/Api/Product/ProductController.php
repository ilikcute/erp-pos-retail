<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductVariantResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('product.product.view');

        $products = $this->productService->paginate(
            filters: $request->only(['search', 'category_id', 'brand_id', 'is_active', 'is_sellable']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('product.product.view');

        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function findByBarcode(string $barcode): JsonResponse
    {
        $this->authorize('product.product.view');

        $variant = $this->productService->findByBarcode($barcode);
        abort_if(! $variant, 404, "Barcode '{$barcode}' tidak ditemukan.");
        abort_if(! $variant->is_active || ! $variant->product->isSellable(), 422, 'Produk tidak aktif atau tidak bisa dijual.');

        return response()->json(['data' => new ProductVariantResource($variant)]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $product = $this->productService->create($validated);

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Produk berhasil ditambahkan.',
        ], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();
        $product = $this->productService->update($product, $validated);

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Produk berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('product.product.delete');

        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $this->productService->delete($product);

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    public function addVariant(Request $request, int $id): JsonResponse
    {
        $this->authorize('product.product.update');

        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'variant_name' => ['required', 'string', 'max:200'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:product_barcodes,barcode'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'attributes.*.attribute_value_id' => ['required', 'integer', 'exists:product_attribute_values,id'],
        ]);

        $variant = $this->productService->addVariant($product, $validated);

        return response()->json([
            'data' => new ProductVariantResource($variant),
            'message' => 'Varian berhasil ditambahkan.',
        ], 201);
    }
}
