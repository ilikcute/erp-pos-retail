<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\BalanceResource;
use App\Repositories\Inventory\Contracts\BalanceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BalanceController extends Controller
{
    public function __construct(
        private readonly BalanceRepositoryInterface $balanceRepo
    ) {}

    /**
     * GET /inventory/balances
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'low_stock' => 'nullable|boolean',
        ]);

        $balances = $this->balanceRepo->getBalances($validated);

        return BalanceResource::collection($balances);
    }
}
