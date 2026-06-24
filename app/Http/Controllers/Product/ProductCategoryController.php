<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductCategoryRequest;
use App\Http\Requests\Product\UpdateProductCategoryRequest;
use App\Actions\Product\CreateProductCategoryAction;
use App\Actions\Product\UpdateProductCategoryAction;
use App\Actions\Product\DeleteProductCategoryAction;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ProductCategoryController extends Controller
{
    public function __construct(
        private ProductCategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(): Response
    {
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
        if (!$category) abort(404);

        $action->execute($category, $request->validated());

        return redirect()->route('product.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(int $id, DeleteProductCategoryAction $action): RedirectResponse
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) abort(404);

        try {
            $action->execute($category);
            return redirect()->route('product.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\App\Exceptions\BusinessException $e) {
            return redirect()->route('product.categories.index')
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }
}
