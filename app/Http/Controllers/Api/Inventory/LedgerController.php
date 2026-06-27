<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\LedgerResource;
use App\Repositories\Inventory\Contracts\LedgerRepositoryInterface;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function __construct(private readonly LedgerRepositoryInterface $ledgerRepo) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'transaction_type' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        return LedgerResource::collection($this->ledgerRepo->getLedgers($validated));
    }
}
