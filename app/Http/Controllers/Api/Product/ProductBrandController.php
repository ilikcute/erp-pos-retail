<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductBrand;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductBrandController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('product.brand.view');

        $brands = ProductBrand::query()
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where('brand_name', 'like', "%{$s}%")
                    ->orWhere('brand_code', 'like', "%{$s}%")
            )
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $brands->items(),
            'meta' => ['current_page' => $brands->currentPage(), 'last_page' => $brands->lastPage(), 'total' => $brands->total()],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('product.brand.view');
        $brand = ProductBrand::find($id);
        abort_if(! $brand, 404, 'Brand tidak ditemukan.');
        return response()->json(['data' => $brand]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('product.brand.manage');

        $validated = $request->validate([
            'brand_code'  => ['required', 'string', 'max:50', 'unique:product_brands,brand_code'],
            'brand_name'  => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ]);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $brand = ProductBrand::create($validated);
        $this->auditService->log('Product', 'CREATE_BRAND', 'product_brands', $brand->id, [], ['brand_name' => $brand->brand_name]);

        return response()->json(['data' => $brand, 'message' => 'Brand berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('product.brand.manage');
        $brand = ProductBrand::find($id);
        abort_if(! $brand, 404, 'Brand tidak ditemukan.');

        $validated = $request->validate([
            'brand_code'  => ['sometimes', 'string', 'max:50', Rule::unique('product_brands', 'brand_code')->ignore($id)],
            'brand_name'  => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ]);
        $validated['updated_by'] = auth()->id();

        $original = $brand->only(['brand_code', 'brand_name', 'is_active']);
        $brand->update($validated);
        $this->auditService->log('Product', 'UPDATE_BRAND', 'product_brands', $brand->id, $original, $validated);

        return response()->json(['data' => $brand->fresh(), 'message' => 'Brand berhasil diperbarui.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('product.brand.manage');
        $brand = ProductBrand::find($id);
        abort_if(! $brand, 404, 'Brand tidak ditemukan.');
        abort_if($brand->products()->exists(), 422, 'Brand masih digunakan oleh produk.');

        $this->auditService->log('Product', 'DELETE_BRAND', 'product_brands', $brand->id, ['brand_name' => $brand->brand_name], []);
        $brand->delete();

        return response()->json(['message' => 'Brand berhasil dihapus.']);
    }
}
