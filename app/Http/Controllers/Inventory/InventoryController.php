<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLedger;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\InventoryPlanogram;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\InventoryTransferItem;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryAdjustmentItem;
use App\Models\Inventory\InventoryOpname;
use App\Models\Inventory\InventoryOpnameItem;
use App\Models\Inventory\InventoryBatch;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $balances = InventoryBalance::with(['variant.product', 'location'])->get();
        $movements = InventoryLedger::with(['variant.product', 'location'])
            ->orderBy('created_at', 'desc')
            ->get();
        $locations = InventoryLocation::with('parent')->get();
        $variants = ProductVariant::with('product')->get();
        $planograms = InventoryPlanogram::with(['variant.product', 'location'])->get();
        $transfers = InventoryTransfer::with(['source', 'destination', 'items.productVariant.product', 'items.batch'])->orderBy('created_at', 'desc')->get();
        $adjustments = InventoryAdjustment::with(['items.productVariant.product', 'items.batch'])->orderBy('created_at', 'desc')->get();
        $opnames = InventoryOpname::with(['location', 'items.productVariant.product', 'items.batch'])->orderBy('created_at', 'desc')->get();
        $batches = InventoryBatch::with(['variant.product', 'location'])->get();

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
            'adjustments' => $adjustments,
            'opnames' => $opnames,
            'batches' => $batches,
            'activeTab' => $activeTab,
        ]);
    }

    // Locations Actions
    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:inventory_locations,code',
            'name' => 'required|string',
            'type' => 'required|string',
            'is_stock_bearing' => 'required|boolean',
            'is_external' => 'required|boolean',
            'parent_id' => 'nullable|exists:inventory_locations,id',
            'address' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        InventoryLocation::create($validated);

        return back()->with('success', 'Lokasi berhasil dibuat.');
    }

    public function updateLocation(Request $request, int $id): RedirectResponse
    {
        $location = InventoryLocation::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|unique:inventory_locations,code,' . $id,
            'name' => 'required|string',
            'type' => 'required|string',
            'is_stock_bearing' => 'required|boolean',
            'is_external' => 'required|boolean',
            'parent_id' => 'nullable|exists:inventory_locations,id',
            'address' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $location->update($validated);

        return back()->with('success', 'Lokasi berhasil diperbarui.');
    }

    // Transfers Actions
    public function storeTransfer(Request $request): RedirectResponse
    {
        $request->validate([
            'source_location_id' => 'required|exists:inventory_locations,id',
            'destination_location_id' => 'required|exists:inventory_locations,id|different:source_location_id',
            'transfer_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_batch_id' => 'required|exists:inventory_batches,id',
            'items.*.transfer_qty' => 'required|numeric|min:0.01',
        ]);

        $source = InventoryLocation::findOrFail($request->source_location_id);
        $destination = InventoryLocation::findOrFail($request->destination_location_id);
        if (!$source->isStockBearing() || !$destination->isStockBearing()) {
            return back()->with('error', 'Transfer stok hanya dapat dilakukan antara lokasi stock-bearing.');
        }

        DB::transaction(function() use ($request) {
            $latest = InventoryTransfer::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $number = 'TRF-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $transfer = InventoryTransfer::create([
                'transfer_number' => $number,
                'source_location_id' => $request->source_location_id,
                'destination_location_id' => $request->destination_location_id,
                'transfer_date' => $request->transfer_date,
                'remarks' => $request->remarks,
                'status' => 'DRAFT',
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                $batch = InventoryBatch::findOrFail($item['inventory_batch_id']);
                InventoryTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'inventory_batch_id' => $item['inventory_batch_id'],
                    'product_variant_id' => $batch->product_variant_id,
                    'transfer_qty' => $item['transfer_qty'],
                ]);
            }
        });

        return back()->with('success', 'Transfer stok berhasil dibuat.');
    }

    public function postTransfer(int $id): RedirectResponse
    {
        $transfer = InventoryTransfer::findOrFail($id);
        if ($transfer->status !== 'DRAFT') {
            return back()->with('error', 'Hanya transfer DRAFT yang dapat diposting.');
        }

        DB::transaction(function() use ($transfer) {
            $transfer->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($transfer->items as $item) {
                $sourceBalance = InventoryBalance::where([
                    'location_id' => $transfer->source_location_id,
                    'product_variant_id' => $item->product_variant_id,
                ])->first();

                if ($sourceBalance) {
                    $sourceBalance->decrement('qty_on_hand', $item->transfer_qty);
                    $sourceBalance->decrement('qty_available', $item->transfer_qty);
                }

                $destBalance = InventoryBalance::firstOrCreate(
                    [
                        'location_id' => $transfer->destination_location_id,
                        'product_variant_id' => $item->product_variant_id,
                    ],
                    [
                        'qty_on_hand' => 0,
                        'qty_reserved' => 0,
                        'qty_available' => 0,
                    ]
                );
                $destBalance->increment('qty_on_hand', $item->transfer_qty);
                $destBalance->increment('qty_available', $item->transfer_qty);

                $sourceBatch = $item->batch;
                if ($sourceBatch) {
                    $sourceBatch->decrement('quantity', $item->transfer_qty);
                    
                    $destBatch = InventoryBatch::firstOrCreate(
                        [
                            'location_id' => $transfer->destination_location_id,
                            'product_variant_id' => $item->product_variant_id,
                            'batch_no' => $sourceBatch->batch_no,
                        ],
                        [
                            'expiry_date' => $sourceBatch->expiry_date,
                            'quantity' => 0,
                            'unit_cost' => $sourceBatch->unit_cost,
                            'is_active' => true,
                        ]
                    );
                    $destBatch->increment('quantity', $item->transfer_qty);
                }

                InventoryLedger::create([
                    'reference_number' => $transfer->transfer_number,
                    'transaction_type' => \App\Enums\Inventory\TransactionType::TRANSFER_OUT ?? 'TRANSFER_OUT',
                    'product_variant_id' => $item->product_variant_id,
                    'location_id' => $transfer->source_location_id,
                    'inventory_batch_id' => $item->inventory_batch_id,
                    'qty_change' => -$item->transfer_qty,
                    'qty_before' => $sourceBalance ? $sourceBalance->qty_on_hand + $item->transfer_qty : 0,
                    'qty_after' => $sourceBalance ? $sourceBalance->qty_on_hand : 0,
                    'unit_cost' => $sourceBatch ? $sourceBatch->unit_cost : 0,
                    'reference_type' => InventoryTransfer::class,
                    'reference_id' => $transfer->id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_date' => now(),
                ]);

                InventoryLedger::create([
                    'reference_number' => $transfer->transfer_number,
                    'transaction_type' => \App\Enums\Inventory\TransactionType::TRANSFER_IN ?? 'TRANSFER_IN',
                    'product_variant_id' => $item->product_variant_id,
                    'location_id' => $transfer->destination_location_id,
                    'inventory_batch_id' => $item->inventory_batch_id,
                    'qty_change' => $item->transfer_qty,
                    'qty_before' => $destBalance->qty_on_hand - $item->transfer_qty,
                    'qty_after' => $destBalance->qty_on_hand,
                    'unit_cost' => $sourceBatch ? $sourceBatch->unit_cost : 0,
                    'reference_type' => InventoryTransfer::class,
                    'reference_id' => $transfer->id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_date' => now(),
                ]);
            }
        });

        return back()->with('success', 'Transfer stok berhasil diposting.');
    }

    public function cancelTransfer(int $id): RedirectResponse
    {
        $transfer = InventoryTransfer::findOrFail($id);
        if ($transfer->status !== 'DRAFT') {
            return back()->with('error', 'Hanya transfer DRAFT yang dapat dibatalkan.');
        }
        $transfer->update(['status' => 'CANCELLED']);
        return back()->with('success', 'Transfer stok berhasil dibatalkan.');
    }

    // Adjustments Actions
    public function storeAdjustment(Request $request): RedirectResponse
    {
        $request->validate([
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:PLUS,MINUS',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_batch_id' => 'required|exists:inventory_batches,id',
            'items.*.adjustment_qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $latest = InventoryAdjustment::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $number = 'ADJ-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $adj = InventoryAdjustment::create([
                'adjustment_number' => $number,
                'adjustment_date' => $request->adjustment_date,
                'adjustment_type' => $request->adjustment_type,
                'reason' => $request->reason,
                'status' => 'PENDING',
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                $batch = InventoryBatch::findOrFail($item['inventory_batch_id']);
                InventoryAdjustmentItem::create([
                    'adjustment_id' => $adj->id,
                    'inventory_batch_id' => $item['inventory_batch_id'],
                    'product_variant_id' => $batch->product_variant_id,
                    'adjustment_qty' => $item['adjustment_qty'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Adjustment stok berhasil dibuat.');
    }

    public function approveAdjustment(int $id): RedirectResponse
    {
        $adj = InventoryAdjustment::findOrFail($id);
        if ($adj->status !== 'PENDING') {
            return back()->with('error', 'Hanya adjustment PENDING yang dapat disetujui.');
        }

        DB::transaction(function() use ($adj) {
            $adj->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'posted_at' => now(),
            ]);

            foreach ($adj->items as $item) {
                $batch = $item->batch;
                $locationId = $batch->location_id ?? 1;

                $balance = InventoryBalance::firstOrCreate(
                    [
                        'location_id' => $locationId,
                        'product_variant_id' => $item->product_variant_id,
                    ],
                    [
                        'qty_on_hand' => 0,
                        'qty_reserved' => 0,
                        'qty_available' => 0,
                    ]
                );

                $qtyChange = $adj->adjustment_type === 'PLUS' ? $item->adjustment_qty : -$item->adjustment_qty;

                $qtyBefore = $balance->qty_on_hand;
                $balance->increment('qty_on_hand', $qtyChange);
                $balance->increment('qty_available', $qtyChange);
                $qtyAfter = $balance->qty_on_hand;

                if ($batch) {
                    $batch->increment('quantity', $qtyChange);
                }

                InventoryLedger::create([
                    'reference_number' => $adj->adjustment_number,
                    'transaction_type' => $adj->adjustment_type === 'PLUS' ? \App\Enums\Inventory\TransactionType::ADJUSTMENT_IN ?? 'ADJUSTMENT_IN' : \App\Enums\Inventory\TransactionType::ADJUSTMENT_OUT ?? 'ADJUSTMENT_OUT',
                    'product_variant_id' => $item->product_variant_id,
                    'location_id' => $locationId,
                    'inventory_batch_id' => $item->inventory_batch_id,
                    'qty_change' => $qtyChange,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'unit_cost' => $batch ? $batch->unit_cost : 0,
                    'reference_type' => InventoryAdjustment::class,
                    'reference_id' => $adj->id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_date' => now(),
                ]);
            }
        });

        return back()->with('success', 'Adjustment stok berhasil disetujui & diposting ke kartu stok.');
    }

    public function rejectAdjustment(Request $request, int $id): RedirectResponse
    {
        $request->validate(['rejection_notes' => 'required|string']);
        $adj = InventoryAdjustment::findOrFail($id);
        if ($adj->status !== 'PENDING') {
            return back()->with('error', 'Hanya adjustment PENDING yang dapat ditolak.');
        }
        $adj->update([
            'status' => 'REJECTED',
            'rejection_notes' => $request->rejection_notes,
        ]);
        return back()->with('success', 'Adjustment stok berhasil ditolak.');
    }

    // Opnames Actions
    public function storeOpname(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_location_id' => 'required|exists:inventory_locations,id',
            'opname_date' => 'required|date',
        ]);

        DB::transaction(function() use ($request) {
            $latest = InventoryOpname::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $number = 'OPN-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $opname = InventoryOpname::create([
                'opname_number' => $number,
                'inventory_location_id' => $request->inventory_location_id,
                'opname_date' => $request->opname_date,
                'status' => 'DRAFT',
                'created_by' => auth()->id() ?? 1,
            ]);

            $batches = InventoryBatch::where('location_id', $request->inventory_location_id)->get();
            foreach ($batches as $batch) {
                InventoryOpnameItem::create([
                    'opname_id' => $opname->id,
                    'inventory_batch_id' => $batch->id,
                    'product_variant_id' => $batch->product_variant_id,
                    'system_qty' => $batch->quantity ?? 0,
                    'physical_qty' => null,
                    'difference' => 0,
                ]);
            }
        });

        return back()->with('success', 'Stock Opname berhasil dimulai.');
    }

    public function countOpname(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventory_opname_items,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            foreach ($request->items as $item) {
                $opnameItem = InventoryOpnameItem::findOrFail($item['id']);
                $diff = $item['physical_qty'] - $opnameItem->system_qty;
                $opnameItem->update([
                    'physical_qty' => $item['physical_qty'],
                    'difference' => $diff,
                ]);
            }
        });

        return back()->with('success', 'Hasil hitung fisik berhasil disimpan.');
    }

    public function approveOpname(int $id): RedirectResponse
    {
        $opname = InventoryOpname::findOrFail($id);
        if ($opname->status !== 'DRAFT') {
            return back()->with('error', 'Hanya opname DRAFT yang dapat disetujui.');
        }
        $opname->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Stock Opname berhasil disetujui.');
    }

    public function postOpname(int $id): RedirectResponse
    {
        $opname = InventoryOpname::findOrFail($id);
        if ($opname->status !== 'APPROVED') {
            return back()->with('error', 'Hanya opname APPROVED yang dapat diposting.');
        }

        DB::transaction(function() use ($opname) {
            $opname->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($opname->items as $item) {
                if ($item->physical_qty === null) continue;

                $batch = $item->batch;
                $diff = $item->difference;

                if ($diff == 0) continue;

                if ($batch) {
                    $batch->update(['quantity' => $item->physical_qty]);
                }

                $balance = InventoryBalance::where([
                    'location_id' => $opname->inventory_location_id,
                    'product_variant_id' => $item->product_variant_id,
                ])->first();

                if ($balance) {
                    $qtyBefore = $balance->qty_on_hand;
                    $balance->increment('qty_on_hand', $diff);
                    $balance->increment('qty_available', $diff);
                    $qtyAfter = $balance->qty_on_hand;
                } else {
                    $qtyBefore = 0;
                    $balance = InventoryBalance::create([
                        'location_id' => $opname->inventory_location_id,
                        'product_variant_id' => $item->product_variant_id,
                        'qty_on_hand' => $item->physical_qty,
                        'qty_reserved' => 0,
                        'qty_available' => $item->physical_qty,
                    ]);
                    $qtyAfter = $item->physical_qty;
                }

                InventoryLedger::create([
                    'reference_number' => $opname->opname_number,
                    'transaction_type' => \App\Enums\Inventory\TransactionType::OPNAME ?? 'OPNAME',
                    'product_variant_id' => $item->product_variant_id,
                    'location_id' => $opname->inventory_location_id,
                    'inventory_batch_id' => $item->inventory_batch_id,
                    'qty_change' => $diff,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'unit_cost' => $batch ? $batch->unit_cost : 0,
                    'reference_type' => InventoryOpname::class,
                    'reference_id' => $opname->id,
                    'user_id' => auth()->id() ?? 1,
                    'transaction_date' => now(),
                ]);
            }
        });

        return back()->with('success', 'Stock Opname berhasil diposting. Saldo stok disesuaikan.');
    }

    // Planograms Actions
    public function storePlanogram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'position_code' => 'required|string',
            'notes' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        InventoryPlanogram::create($validated);

        return back()->with('success', 'Planogram berhasil dibuat.');
    }

    public function updatePlanogram(Request $request, int $id): RedirectResponse
    {
        $plan = InventoryPlanogram::findOrFail($id);
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'position_code' => 'required|string',
            'notes' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $plan->update($validated);

        return back()->with('success', 'Planogram berhasil diperbarui.');
    }

    public function destroyPlanogram(int $id): RedirectResponse
    {
        $plan = InventoryPlanogram::findOrFail($id);
        $plan->delete();
        return back()->with('success', 'Planogram berhasil dihapus.');
    }
}
