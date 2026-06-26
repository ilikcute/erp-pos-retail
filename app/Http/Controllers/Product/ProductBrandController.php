<?php

namespace App\Http\Controllers\Product;

use App\Actions\Product\CreateProductBrandAction;
use App\Actions\Product\DeleteProductBrandAction;
use App\Actions\Product\UpdateProductBrandAction;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductBrandRequest;
use App\Http\Requests\Product\UpdateProductBrandRequest;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductBrandController extends Controller
{
    public function __construct(
        private ProductBrandRepositoryInterface $brandRepository
    ) {}

    public function index(): Response
    {
        $brands = $this->brandRepository->paginate(request()->only('search'), 15);

        return Inertia::render('Product/Brands/Index', [
            'brands' => $brands,
        ]);
    }

    public function store(StoreProductBrandRequest $request, CreateProductBrandAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('product.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function update(int $id, UpdateProductBrandRequest $request, UpdateProductBrandAction $action): RedirectResponse
    {
        $brand = $this->brandRepository->findById($id);
        if (! $brand) {
            abort(404);
        }

        $action->execute($brand, $request->validated());

        return redirect()->route('product.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(int $id, DeleteProductBrandAction $action): RedirectResponse
    {
        $brand = $this->brandRepository->findById($id);
        if (! $brand) {
            abort(404);
        }

        try {
            $action->execute($brand);

            return redirect()->route('product.brands.index')
                ->with('success', 'Brand deleted successfully.');
        } catch (BusinessException $e) {
            return redirect()->route('product.brands.index')
                ->withErrors(['delete' => $e->getMessage()]);
        }
    }
}
