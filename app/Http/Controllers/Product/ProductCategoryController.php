<?php

namespace App\Http\Controllers\Product;

use App\Actions\Product\CreateProductCategoryAction;
use App\Actions\Product\DeleteProductCategoryAction;
use App\Actions\Product\UpdateProductCategoryAction;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductCategoryRequest;
use App\Http\Requests\Product\UpdateProductCategoryRequest;
use App\Models\System\User;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProductCategoryController extends Controller
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(): Response
    {
        $this->authorize('product.category.view');

        $categories = $this->categoryRepository->getTree();
        $flatCategories = $this->categoryRepository->getFlatList();

        return Inertia::render('Product/Categories/Index', [
            'categories' => $categories,
            'flatCategories' => $flatCategories, // Untuk dropdown parent
        ]);
    }

    public function store(StoreProductCategoryRequest $request, CreateProductCategoryAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('product.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function update(int $id, UpdateProductCategoryRequest $request, UpdateProductCategoryAction $action): RedirectResponse
    {
        $category = $this->categoryRepository->findById($id);
        if (! $category) {
            abort(404);
        }

        $action->execute($category, $request->validated());

        return redirect()->route('product.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, int $id, DeleteProductCategoryAction $action): RedirectResponse
    {
        $user = auth()->user();
        $category = $this->categoryRepository->findById($id);
        if (! $category) {
            abort(404);
        }

        // Jika bukan level tinggi atau tidak memiliki permission manage, wajib memvalidasi kredensial supervisor
        if (! $user->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) && ! $user->hasPermission('product.category.manage')) {
            $request->validate([
                'supervisor_email' => 'required|email|exists:users,email',
                'supervisor_password' => 'required|string',
            ]);

            $supervisor = User::where('email', $request->supervisor_email)->first();

            if (! $supervisor || ! Hash::check($request->supervisor_password, $supervisor->password)) {
                return redirect()->back()->withErrors(['delete' => 'Kredensial supervisor salah atau tidak ditemukan.']);
            }

            if (! $supervisor->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) && ! $supervisor->hasPermission('product.category.manage')) {
                return redirect()->back()->withErrors(['delete' => 'User supervisor yang dimasukkan tidak memiliki wewenang menghapus kategori.']);
            }
        }

        try {
            $action->execute($category);

            return redirect()->route('product.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (BusinessException $e) {
            return redirect()->route('product.categories.index')
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }
}
