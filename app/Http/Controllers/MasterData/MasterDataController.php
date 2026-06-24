<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use App\Repositories\Contracts\MasterData\CustomerCategoryRepositoryInterface;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function __construct(
        private readonly SupplierRepositoryInterface $supplierRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CustomerCategoryRepositoryInterface $customerCategoryRepository,
        private readonly UnitRepositoryInterface $unitRepository
    ) {}

    public function suppliers(Request $request): Response
    {
        $this->authorize('master-data.supplier.view');

        $suppliers = $this->supplierRepository->listActive();
        
        // Fallback to mock data if database is empty
        if ($suppliers->isEmpty()) {
            $suppliers = collect([
                [
                    'id' => 1,
                    'supplier_name' => 'PT. Mega Distribusi Utama',
                    'contact_person' => 'Hendra Wijaya',
                    'phone' => '021-5551234',
                    'email' => 'sales@megadistribusi.co.id',
                    'is_active' => true,
                ],
                [
                    'id' => 2,
                    'supplier_name' => 'CV. Jaya Abadi Selaras',
                    'contact_person' => 'Santi Rahayu',
                    'phone' => '031-8884321',
                    'email' => 'santi@jayaabadi.com',
                    'is_active' => true,
                ],
                [
                    'id' => 3,
                    'supplier_name' => 'PT. Pangan Nusantara',
                    'contact_person' => 'Budi Pratama',
                    'phone' => '022-7776543',
                    'email' => 'budi@pangannusantara.id',
                    'is_active' => true,
                ]
            ]);
        }

        $units = $this->unitRepository->listActive();
        if ($units->isEmpty()) {
            $units = collect([
                ['id' => 1, 'code' => 'PCS', 'name' => 'Pieces', 'is_base' => true],
                ['id' => 2, 'code' => 'BOX', 'name' => 'Box / Dus', 'is_base' => false],
                ['id' => 3, 'code' => 'PACK', 'name' => 'Pack', 'is_base' => false],
            ]);
        }

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'suppliers',
            'suppliers' => $suppliers,
            'customers' => [],
            'categories' => [],
            'units' => $units,
        ]);
    }

    public function customers(Request $request): Response
    {
        $this->authorize('master-data.customer.view');

        $customers = $this->customerRepository->paginate(filters: [], perPage: 50)->items();
        $customers = collect($customers);

        if ($customers->isEmpty()) {
            $customers = collect([
                [
                    'id' => 1,
                    'customer_name' => 'Andi Susanto',
                    'category' => ['name' => 'VIP Member'],
                    'phone' => '081234567890',
                    'email' => 'andi.susanto@gmail.com',
                    'loyalty_account' => ['current_points' => 1250],
                ],
                [
                    'id' => 2,
                    'customer_name' => 'Dewi Lestari',
                    'category' => ['name' => 'Regular Member'],
                    'phone' => '087888123456',
                    'email' => 'dewi.lestari@yahoo.com',
                    'loyalty_account' => ['current_points' => 320],
                ],
                [
                    'id' => 3,
                    'customer_name' => 'Rian Hidayat',
                    'category' => ['name' => 'Grosir / Reseller'],
                    'phone' => '085299887766',
                    'email' => 'rian.hidayat@outlook.com',
                    'loyalty_account' => ['current_points' => 8450],
                ]
            ]);
        }

        $categories = $this->customerCategoryRepository->listAll();
        if ($categories->isEmpty()) {
            $categories = collect([
                ['id' => 1, 'name' => 'Regular Member', 'description' => 'Standard retail customers'],
                ['id' => 2, 'name' => 'VIP Member', 'description' => 'Premium customers with special discounts'],
                ['id' => 3, 'name' => 'Grosir / Reseller', 'description' => 'B2B Wholesale purchasers'],
            ]);
        }

        return Inertia::render('MasterData/Index', [
            'activeTab' => 'customers',
            'suppliers' => [],
            'customers' => $customers,
            'categories' => $categories,
            'units' => [],
        ]);
    }
}
