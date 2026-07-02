<?php

namespace App\Http\Controllers\Product;

use App\Actions\Product\CreateProductBrandAction;
use App\Actions\Product\DeleteProductBrandAction;
use App\Actions\Product\UpdateProductBrandAction;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductBrandRequest;
use App\Http\Requests\Product\UpdateProductBrandRequest;
use App\Models\System\User;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProductBrandController extends Controller
{
    public function __construct(
        private ProductBrandRepositoryInterface $brandRepository
    ) {}

    public function index(): Response
    {
        $this->authorize('product.brand.view');

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

    public function destroy(Request $request, int $id, DeleteProductBrandAction $action): RedirectResponse
    {
        $user = auth()->user();
        $brand = $this->brandRepository->findById($id);
        if (! $brand) {
            abort(404);
        }

        // Jika bukan level tinggi atau tidak memiliki permission manage, wajib memvalidasi kredensial supervisor
        if (! $user->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) && ! $user->hasPermission('product.brand.manage')) {
            $request->validate([
                'supervisor_email' => 'required|email|exists:users,email',
                'supervisor_password' => 'required|string',
            ]);

            $supervisor = User::where('email', $request->supervisor_email)->first();

            if (! $supervisor || ! Hash::check($request->supervisor_password, $supervisor->password)) {
                return redirect()->back()->withErrors(['delete' => 'Kredensial supervisor salah atau tidak ditemukan.']);
            }

            if (! $supervisor->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) && ! $supervisor->hasPermission('product.brand.manage')) {
                return redirect()->back()->withErrors(['delete' => 'User supervisor yang dimasukkan tidak memiliki wewenang menghapus brand.']);
            }
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
