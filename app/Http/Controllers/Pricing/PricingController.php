<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
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

        $priceChangeRequests = $this->priceChangeRequestRepository->paginate(filters: [], perPage: 50)->items();
        $priceChangeRequests = collect($priceChangeRequests);

        return Inertia::render('Pricing/Index', [
            'priceLists' => $priceLists,
            'priceChangeRequests' => $priceChangeRequests,
        ]);
    }
}
