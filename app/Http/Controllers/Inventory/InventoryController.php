<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLedger;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\InventoryPlanogram;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $balances = InventoryBalance::with(['productVariant.product', 'location'])->get();
        $movements = InventoryLedger::with(['productVariant.product', 'location'])
            ->orderBy('created_at', 'desc')
            ->get();
        $locations = InventoryLocation::all();
        $variants = ProductVariant::with('product')->get();
        $planograms = InventoryPlanogram::with(['variant.product', 'location'])->get();
        $transfers = \App\Models\Inventory\InventoryTransfer::with(['source', 'destination'])->get();

        $activeTab = $request->query('activeTab', 'balance');
        if ($request->is('inventory/transfer')) {
            $activeTab = 'transfers';
        }

        return Inertia::render('Inventory/Index', [
            'inventoryBalance' => $balances,
            'movements' => $movements,
            'locations' => $locations,
            'variants' => $variants,
            'planograms' => $planograms,
            'transfers' => $transfers,
            'activeTab' => $activeTab,
        ]);
    }
}
