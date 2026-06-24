<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\Product\ProductService;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductBrandRepositoryInterface $brandRepository,
        private readonly ProductCategoryRepositoryInterface $categoryRepository,
        private readonly UnitRepositoryInterface $unitRepository,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('product.product.view');

        $products = $this->productRepository->paginate(
            filters: $request->only(['search', 'category_id', 'brand_id', 'is_active', 'is_sellable']),
            perPage: $request->integer('per_page', 10),
        );

        $brands = $this->brandRepository->listActive();
        $categories = $this->categoryRepository->getFlatList();

        return Inertia::render('Product/Products/Index', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'brand_id', 'is_active', 'is_sellable']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('product.product.create');

        $brands = $this->brandRepository->listActive();
        $categories = $this->categoryRepository->getFlatList();
        $units = $this->unitRepository->listActive();

        return Inertia::render('Product/Products/Create', [
            'brands' => $brands,
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $this->productService->create($validated);

        return redirect()->route('product.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(int $id): Response
    {
        $this->authorize('product.product.update');

        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $brands = $this->brandRepository->listActive();
        $categories = $this->categoryRepository->getFlatList();
        $units = $this->unitRepository->listActive();

        return Inertia::render('Product/Products/Edit', [
            'product' => $product,
            'brands' => $brands,
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();
        
        $this->productService->update($product, $validated);

        return redirect()->route('product.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorize('product.product.delete');

        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $this->productService->delete($product);

        return redirect()->route('product.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function addVariant(Request $request, int $id): RedirectResponse
    {
        $this->authorize('product.product.update');

        $product = $this->productService->findById($id);
        abort_if(! $product, 404, 'Produk tidak ditemukan.');

        $validated = $request->validate([
            'sku'            => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'variant_name'   => ['required', 'string', 'max:200'],
            'barcode'        => ['nullable', 'string', 'max:100', 'unique:product_barcodes,barcode'],
            'weight'         => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'attributes'     => ['nullable', 'array'],
            'attributes.*.attribute_id'      => ['required', 'integer', 'exists:product_attributes,id'],
            'attributes.*.attribute_value_id' => ['required', 'integer', 'exists:product_attribute_values,id'],
        ]);

        $this->productService->addVariant($product, $validated);

        return redirect()->back()->with('success', 'Varian berhasil ditambahkan.');
    }

    public function updateVariant(Request $request, int $productId, int $variantId): RedirectResponse
    {
        $this->authorize('product.product.update');

        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'sku'            => ['required', 'string', 'max:100', 'unique:product_variants,sku,' . $variantId],
            'variant_name'   => ['required', 'string', 'max:200'],
            'barcode'        => ['nullable', 'string', 'max:100', 'unique:product_barcodes,barcode,' . $variant->primaryBarcode?->id],
            'barcode_type'   => ['nullable', 'in:EAN13,EAN8,QR,CODE128,CUSTOM'],
            'weight'         => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'is_active'      => ['boolean'],
        ]);

        $this->productService->updateVariant($variant, $validated);

        return redirect()->back()->with('success', 'Varian berhasil diperbarui.');
    }

    public function deleteVariant(int $productId, int $variantId): RedirectResponse
    {
        $this->authorize('product.product.update');

        $variant = ProductVariant::findOrFail($variantId);

        try {
            $this->productService->deleteVariant($variant);
            return redirect()->back()->with('success', 'Varian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['delete_variant' => $e->getMessage()]);
        }
    }
}
