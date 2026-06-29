<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseOrderItem;
use App\Models\MasterData\Supplier;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PurchasingController extends Controller
{
    public function index(Request $request): Response
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->get();

        $routeName = $request->route() ? $request->route()->getName() : null;
        $activeTab = $request->query('activeTab');
        if (!$activeTab) {
            if ($routeName === 'purchasing.receipt') {
                $activeTab = 'receipts';
            } else {
                $activeTab = 'orders';
            }
        }

        return Inertia::render('Purchasing/Index', [
            'purchaseOrders' => $purchaseOrders,
            'activeTab' => $activeTab,
        ]);
    }

    public function create(): Response
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $variants = ProductVariant::with('product')
            ->where('is_active', true)
            ->get();
        $units = Unit::get();

        return Inertia::render('Purchasing/Create', [
            'suppliers' => $suppliers,
            'variants' => $variants,
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'required|date|after_or_equal:order_date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.ordered_qty' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate sequence number
            $latestPo = PurchaseOrder::latest('id')->first();
            $nextId = $latestPo ? $latestPo->id + 1 : 1;
            $poNumber = 'PO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'status' => 'DRAFT',
                'remarks' => $request->remarks,
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'created_by' => auth()->id() ?? 1,
            ]);

            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($request->items as $item) {
                $lineTotal = ($item['ordered_qty'] * $item['unit_cost']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'ordered_qty' => $item['ordered_qty'],
                    'received_qty' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                $subtotal += ($item['ordered_qty'] * $item['unit_cost']);
                $totalDiscount += ($item['discount_amount'] ?? 0);
                $totalTax += ($item['tax_amount'] ?? 0);
            }

            $po->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);
        });

        return redirect()->route('purchasing.po')->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show($id): Response
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.productVariant.product', 'creator', 'approver'])
            ->findOrFail($id);

        return Inertia::render('Purchasing/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function approve($id): RedirectResponse
    {
        $po = PurchaseOrder::findOrFail($id);
        
        if ($po->status !== 'DRAFT') {
            return back()->with('error', 'Hanya Purchase Order DRAFT yang dapat disetujui.');
        }

        $po->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase Order berhasil disetujui.');
    }

    public function cancel($id): RedirectResponse
    {
        $po = PurchaseOrder::findOrFail($id);

        if (in_array($po->status, ['RECEIVED', 'CANCELLED'])) {
            return back()->with('error', 'Purchase Order tidak dapat dibatalkan.');
        }

        $po->update([
            'status' => 'CANCELLED',
        ]);

        return back()->with('success', 'Purchase Order berhasil dibatalkan.');
    }
}
