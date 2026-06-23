<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductCategory;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('product.category.view');

        $categories = ProductCategory::query()
            ->with('parent')
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where('category_name', 'like', "%{$s}%")
                    ->orWhere('category_code', 'like', "%{$s}%")
            )
            ->when($request->boolean('root_only'), fn($q) => $q->root())
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 50));

        return response()->json([
            'data' => $categories->items(),
            'meta' => ['current_page' => $categories->currentPage(), 'total' => $categories->total()],
        ]);
    }

    public function tree(): JsonResponse
    {
        $this->authorize('product.category.view');

        $categories = ProductCategory::with('children.children')
            ->active()->root()->orderBy('sort_order')->get();

        return response()->json(['data' => $categories]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('product.category.view');
        $category = ProductCategory::with(['parent', 'children'])->find($id);
        abort_if(! $category, 404, 'Kategori tidak ditemukan.');
        return response()->json(['data' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('product.category.manage');

        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:50', 'unique:product_categories,category_code'],
            'category_name' => ['required', 'string', 'max:150'],
            'parent_id'     => ['nullable', 'integer', 'exists:product_categories,id'],
            'description'   => ['nullable', 'string', 'max:255'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ]);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $category = ProductCategory::create($validated);
        $this->auditService->log('Product', 'CREATE_CATEGORY', 'product_categories', $category->id, [], ['category_name' => $category->category_name]);

        return response()->json(['data' => $category, 'message' => 'Kategori produk berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('product.category.manage');
        $category = ProductCategory::find($id);
        abort_if(! $category, 404, 'Kategori tidak ditemukan.');

        $validated = $request->validate([
            'category_code' => ['sometimes', 'string', 'max:50', Rule::unique('product_categories', 'category_code')->ignore($id)],
            'category_name' => ['sometimes', 'string', 'max:150'],
            'parent_id'     => ['nullable', 'integer', 'exists:product_categories,id', Rule::notIn([$id])],
            'description'   => ['nullable', 'string', 'max:255'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ]);
        $validated['updated_by'] = auth()->id();

        $original = $category->only(['category_code', 'category_name', 'parent_id', 'is_active']);
        $category->update($validated);
        $this->auditService->log('Product', 'UPDATE_CATEGORY', 'product_categories', $id, $original, $validated);

        return response()->json(['data' => $category->fresh('parent'), 'message' => 'Kategori berhasil diperbarui.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('product.category.manage');
        $category = ProductCategory::find($id);
        abort_if(! $category, 404, 'Kategori tidak ditemukan.');
        abort_if($category->products()->exists(), 422, 'Kategori masih digunakan oleh produk.');
        abort_if($category->children()->exists(), 422, 'Kategori masih memiliki sub-kategori.');

        $this->auditService->log('Product', 'DELETE_CATEGORY', 'product_categories', $id, $category->toArray(), []);
        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
