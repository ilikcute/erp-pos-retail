<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MasterData\CurrencyRepositoryInterface;
use App\Repositories\Contracts\MasterData\CustomerCategoryRepositoryInterface;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use App\Repositories\Contracts\MasterData\TaxRepositoryInterface;
use App\Repositories\Contracts\MasterData\UnitConversionRepositoryInterface;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function __construct(
        private readonly SupplierRepositoryInterface $supplierRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CustomerCategoryRepositoryInterface $customerCategoryRepository,
        private readonly UnitRepositoryInterface $unitRepository,
        private readonly TaxRepositoryInterface $taxRepository,
        private readonly CurrencyRepositoryInterface $currencyRepository,
        private readonly UnitConversionRepositoryInterface $unitConversionRepository,
        private readonly PriceListRepositoryInterface $priceListRepository
    ) {}

    public function suppliers(Request $request): Response
    {
        $this->authorize('master-data.supplier.view');
        $suppliers = $this->supplierRepository->listActive();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'suppliers',
            'suppliers' => $suppliers,
        ]);
    }

    public function customers(Request $request): Response
    {
        $this->authorize('master-data.customer.view');
        $customers = $this->customerRepository->paginate([], 100)->items();
        $categories = $this->customerCategoryRepository->listAll();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'customers',
            'customers' => $customers,
            'categories' => $categories,
        ]);
    }

    public function customerCategories(Request $request): Response
    {
        $this->authorize('master-data.customer.view');
        $categories = $this->customerCategoryRepository->listAll();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'customer-categories',
            'categories' => $categories,
        ]);
    }

    public function currencies(Request $request): Response
    {
        $currencies = $this->currencyRepository->listAll();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'currencies',
            'currencies' => $currencies,
        ]);
    }

    public function taxes(Request $request): Response
    {
        $taxes = $this->taxRepository->paginate([], 100)->items();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'taxes',
            'taxes' => $taxes,
        ]);
    }

    public function units(Request $request): Response
    {
        $units = $this->unitRepository->listAll();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'units',
            'units' => $units,
        ]);
    }

    public function unitConversions(Request $request): Response
    {
        $conversions = $this->unitConversionRepository->listAll();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'unit-conversions',
            'conversions' => $conversions,
        ]);
    }

    public function priceLists(Request $request): Response
    {
        $priceLists = $this->priceListRepository->paginate([], 100)->items();

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'price-lists',
            'priceLists' => $priceLists,
        ]);
    }
}
