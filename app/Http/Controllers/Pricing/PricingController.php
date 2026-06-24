<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function __construct(
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly PriceChangeRequestRepositoryInterface $priceChangeRequestRepository
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('pricing.price-list.view');

        $priceLists = $this->priceListRepository->paginate(filters: [], perPage: 50)->items();
        $priceLists = collect($priceLists);

        if ($priceLists->isEmpty()) {
            $priceLists = collect([
                [
                    'id' => 1,
                    'price_list_name' => 'General Price List (Retail)',
                    'price_list_type' => 'RETAIL',
                    'items' => array_fill(0, 15, null), // 15 items mock
                    'is_active' => true,
                ],
                [
                    'id' => 2,
                    'price_list_name' => 'Wholesale Price List (Grosir)',
                    'price_list_type' => 'WHOLESALE',
                    'items' => array_fill(0, 8, null), // 8 items mock
                    'is_active' => true,
                ],
                [
                    'id' => 3,
                    'price_list_name' => 'Member Promo Price List',
                    'price_list_type' => 'PROMO',
                    'items' => array_fill(0, 5, null), // 5 items mock
                    'is_active' => true,
                ]
            ]);
        }

        $priceChangeRequests = $this->priceChangeRequestRepository->paginate(filters: [], perPage: 50)->items();
        $priceChangeRequests = collect($priceChangeRequests);

        if ($priceChangeRequests->isEmpty()) {
            $priceChangeRequests = collect([
                [
                    'id' => 1,
                    'request_no' => 'PCR-2026-0001',
                    'price_list' => ['price_list_name' => 'General Price List (Retail)'],
                    'effective_date' => '2026-07-01',
                    'status' => 'APPROVED',
                ],
                [
                    'id' => 2,
                    'request_no' => 'PCR-2026-0002',
                    'price_list' => ['price_list_name' => 'Member Promo Price List'],
                    'effective_date' => '2026-07-15',
                    'status' => 'PENDING',
                ],
                [
                    'id' => 3,
                    'request_no' => 'PCR-2026-0003',
                    'price_list' => ['price_list_name' => 'Wholesale Price List (Grosir)'],
                    'effective_date' => '2026-08-01',
                    'status' => 'DRAFT',
                ]
            ]);
        }

        return Inertia::render('Pricing/Index', [
            'priceLists' => $priceLists,
            'priceChangeRequests' => $priceChangeRequests,
        ]);
    }
}
