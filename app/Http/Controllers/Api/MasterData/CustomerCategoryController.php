<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\CustomerCategory;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerCategoryController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('master-data.customer.view');

        $categories = CustomerCategory::query()
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where('category_name', 'like', "%{$s}%")
                    ->orWhere('category_code', 'like', "%{$s}%")
            )
            ->when(isset($request->is_active), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('master-data.customer.view');

        $category = CustomerCategory::find($id);
        abort_if(! $category, 404, 'Kategori customer tidak ditemukan.');

        return response()->json(['data' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('master-data.customer.create');

        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:50', 'unique:customer_categories,category_code'],
            'category_name' => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:255'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $category = CustomerCategory::create($validated);
        $this->auditService->log('MasterData', 'CREATE_CUSTOMER_CATEGORY', 'customer_categories', $category->id, [], $validated);

        return response()->json(['data' => $category, 'message' => 'Kategori customer berhasil dibuat.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('master-data.customer.update');

        $category = CustomerCategory::find($id);
        abort_if(! $category, 404, 'Kategori customer tidak ditemukan.');

        $validated = $request->validate([
            'category_code' => ['sometimes', 'string', 'max:50', Rule::unique('customer_categories', 'category_code')->ignore($id)],
            'category_name' => ['sometimes', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:255'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $original = $category->only(['category_code', 'category_name', 'is_active']);
        $category->update($validated);
        $this->auditService->log('MasterData', 'UPDATE_CUSTOMER_CATEGORY', 'customer_categories', $id, $original, $validated);

        return response()->json(['data' => $category->fresh(), 'message' => 'Kategori customer berhasil diperbarui.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('master-data.customer.delete');

        $category = CustomerCategory::find($id);
        abort_if(! $category, 404, 'Kategori customer tidak ditemukan.');
        abort_if($category->customers()->exists(), 422, 'Kategori masih digunakan oleh customer.');

        $this->auditService->log('MasterData', 'DELETE_CUSTOMER_CATEGORY', 'customer_categories', $id, $category->toArray(), []);
        $category->delete();

        return response()->json(['message' => 'Kategori customer berhasil dihapus.']);
    }
}
