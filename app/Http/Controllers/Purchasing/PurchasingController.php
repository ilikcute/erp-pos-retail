<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseOrderItem;
use App\Models\Purchasing\PurchaseRequest;
use App\Models\Purchasing\PurchaseRequestItem;
use App\Models\Purchasing\GoodsReceipt;
use App\Models\Purchasing\GoodsReceiptItem;
use App\Models\Purchasing\SupplierInvoice;
use App\Models\Purchasing\SupplierInvoiceItem;
use App\Models\Purchasing\AccountsPayable;
use App\Models\Purchasing\SupplierPayment;
use App\Models\Purchasing\SupplierPaymentAllocation;
use App\Models\Purchasing\PurchaseReturn;
use App\Models\Purchasing\PurchaseReturnItem;
use App\Models\Purchasing\LandedCost;
use App\Models\Purchasing\SupplierPerformance;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\InventoryBatch;
use App\Models\Accounting\PaymentMethod;
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
        $purchaseRequests = PurchaseRequest::with(['requester', 'items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $goodsReceipts = GoodsReceipt::with(['purchaseOrder', 'location', 'items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $supplierInvoices = SupplierInvoice::with(['supplier', 'goodsReceipt', 'items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $supplierPayments = SupplierPayment::with(['supplier', 'allocations.accountPayable'])
            ->orderBy('created_at', 'desc')
            ->get();

        $accountsPayables = AccountsPayable::with(['supplier', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchaseReturns = PurchaseReturn::with(['goodsReceipt', 'supplier', 'items.productVariant.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $landedCosts = LandedCost::with(['goodsReceipt'])
            ->orderBy('created_at', 'desc')
            ->get();

        $supplierPerformances = SupplierPerformance::with(['supplier', 'evaluator'])
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::where('is_active', true)->get();
        $variants = ProductVariant::with('product')->where('is_active', true)->get();
        $locations = InventoryLocation::all();
        $paymentMethods = PaymentMethod::all();

        $activeTab = $request->query('activeTab', 'orders');

        return Inertia::render('Purchasing/Index', [
            'purchaseRequests' => $purchaseRequests,
            'purchaseOrders' => $purchaseOrders,
            'goodsReceipts' => $goodsReceipts,
            'supplierInvoices' => $supplierInvoices,
            'supplierPayments' => $supplierPayments,
            'accountsPayables' => $accountsPayables,
            'purchaseReturns' => $purchaseReturns,
            'landedCosts' => $landedCosts,
            'supplierPerformances' => $supplierPerformances,
            'suppliers' => $suppliers,
            'variants' => $variants,
            'locations' => $locations,
            'paymentMethods' => $paymentMethods,
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

    // Purchase Request Actions
    public function storeRequest(Request $request): RedirectResponse
    {
        $request->validate([
            'request_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.requested_qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $latest = PurchaseRequest::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $prNumber = 'PR-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $pr = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'request_date' => $request->request_date,
                'requested_by' => auth()->id() ?? 1,
                'status' => 'DRAFT',
                'remarks' => $request->remarks,
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'requested_qty' => $item['requested_qty'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Purchase Request berhasil dibuat.');
    }

    public function approveRequest(int $id): RedirectResponse
    {
        $pr = PurchaseRequest::findOrFail($id);
        if ($pr->status !== 'DRAFT') {
            return back()->with('error', 'Hanya PR berstatus DRAFT yang dapat disetujui.');
        }
        $pr->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Purchase Request berhasil disetujui.');
    }

    public function rejectRequest(Request $request, int $id): RedirectResponse
    {
        $request->validate(['rejection_notes' => 'required|string|max:255']);
        $pr = PurchaseRequest::findOrFail($id);
        if ($pr->status !== 'DRAFT') {
            return back()->with('error', 'Hanya PR berstatus DRAFT yang dapat ditolak.');
        }
        $pr->update([
            'status' => 'REJECTED',
            'rejection_notes' => $request->rejection_notes,
        ]);
        return back()->with('success', 'Purchase Request berhasil ditolak.');
    }

    // Goods Receipt Actions
    public function storeReceipt(Request $request): RedirectResponse
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'receipt_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_no' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        DB::transaction(function() use ($request) {
            $latest = GoodsReceipt::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $grNumber = 'GR-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $gr = GoodsReceipt::create([
                'gr_number' => $grNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'location_id' => $request->location_id,
                'receipt_date' => $request->receipt_date,
                'status' => 'DRAFT',
                'remarks' => $request->remarks,
                'received_by' => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'received_qty' => $item['received_qty'],
                    'unit_cost' => $item['unit_cost'],
                    'batch_no' => $item['batch_no'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Goods Receipt berhasil dibuat.');
  }

    public function postReceipt(int $id): RedirectResponse
    {
        $gr = GoodsReceipt::findOrFail($id);
        if ($gr->status !== 'DRAFT') {
            return back()->with('error', 'Hanya Goods Receipt DRAFT yang dapat diposting.');
        }

        DB::transaction(function() use ($gr) {
            $gr->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($gr->items as $item) {
                $poItem = $item->purchaseOrderItem;
                if ($poItem) {
                    $poItem->increment('received_qty', $item->received_qty);
                }

                // Create Inventory Batch
                $batch = InventoryBatch::create([
                    'product_variant_id' => $item->product_variant_id,
                    'batch_number' => $item->batch_no ?: 'BCH-' . date('Ymd') . '-' . rand(1000, 9999),
                    'expiry_date' => $item->expiry_date,
                    'quantity' => $item->received_qty,
                    'cost_price' => $item->unit_cost,
                ]);
                $item->update(['inventory_batch_id' => $batch->id]);
            }

            // Check if PO is completely received
            $po = $gr->purchaseOrder;
            $allReceived = true;
            foreach ($po->items as $poItem) {
                if ($poItem->received_qty < $poItem->ordered_qty) {
                    $allReceived = false;
                    break;
                }
            }
            if ($allReceived) {
                $po->update(['status' => 'RECEIVED']);
            } else {
                $po->update(['status' => 'PARTIAL']);
            }
        });

        return back()->with('success', 'Goods Receipt berhasil diposting. Stok & batch inventaris diperbarui.');
    }

    // Supplier Invoice Actions
    public function storeInvoice(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'goods_receipt_id' => 'nullable|exists:goods_receipts,id',
            'supplier_invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $latest = SupplierInvoice::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $invoiceNumber = 'SI-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $taxAmount = 0;
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = ($item['qty'] * $item['unit_cost']) + ($item['tax_amount'] ?? 0);
                $subtotal += ($item['qty'] * $item['unit_cost']);
                $taxAmount += ($item['tax_amount'] ?? 0);
                $totalAmount += $lineTotal;
            }

            $invoice = SupplierInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $request->supplier_id,
                'goods_receipt_id' => $request->goods_receipt_id,
                'supplier_invoice_no' => $request->supplier_invoice_no,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'status' => 'DRAFT',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'notes' => $request->remarks,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                $lineTotal = ($item['qty'] * $item['unit_cost']) + ($item['tax_amount'] ?? 0);
                SupplierInvoiceItem::create([
                    'supplier_invoice_id' => $invoice->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'qty' => $item['qty'],
                    'unit_cost' => $item['unit_cost'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => $lineTotal,
                ]);
            }
        });

        return back()->with('success', 'Supplier Invoice berhasil dibuat.');
    }

    public function postInvoice(int $id): RedirectResponse
    {
        $invoice = SupplierInvoice::findOrFail($id);
        if ($invoice->status !== 'DRAFT') {
            return back()->with('error', 'Hanya Supplier Invoice DRAFT yang dapat diposting.');
        }

        DB::transaction(function() use ($invoice) {
            $invoice->update([
                'status' => 'UNPAID',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            // Create Accounts Payable
            $latest = AccountsPayable::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $apNumber = 'AP-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            AccountsPayable::create([
                'payable_number' => $apNumber,
                'supplier_id' => $invoice->supplier_id,
                'invoice_id' => $invoice->id,
                'source_type' => SupplierInvoice::class,
                'source_id' => $invoice->id,
                'transaction_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => 0,
                'remaining_amount' => $invoice->total_amount,
                'status' => 'OPEN',
                'currency' => 'IDR',
                'notes' => 'Generated from Invoice ' . $invoice->invoice_number,
            ]);
        });

        return back()->with('success', 'Supplier Invoice berhasil diposting. Accounts Payable dibentuk.');
    }

    // Supplier Payment Actions
    public function storePayment(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:CASH,TRANSFER,GIRO,CHEQUE',
            'reference_no' => 'nullable|string',
            'payment_method_account_id' => 'nullable|exists:payment_methods,id',
            'remarks' => 'nullable|string',
            'allocations' => 'required|array|min:1',
            'allocations.*.account_payable_id' => 'required|exists:accounts_payables,id',
            'allocations.*.allocated_amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function() use ($request) {
            $latest = SupplierPayment::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $paymentNumber = 'SP-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $totalAmount = collect($request->allocations)->sum('allocated_amount');

            $payment = SupplierPayment::create([
                'payment_number' => $paymentNumber,
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_no' => $request->reference_no,
                'payment_method_account_id' => $request->payment_method_account_id,
                'total_amount' => $totalAmount,
                'status' => 'DRAFT',
                'remarks' => $request->remarks,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($request->allocations as $alloc) {
                SupplierPaymentAllocation::create([
                    'supplier_payment_id' => $payment->id,
                    'account_payable_id' => $alloc['account_payable_id'],
                    'allocated_amount' => $alloc['allocated_amount'],
                ]);
            }
        });

        return back()->with('success', 'Supplier Payment berhasil dibuat.');
    }

    public function postPayment(int $id): RedirectResponse
    {
        $payment = SupplierPayment::findOrFail($id);
        if ($payment->status !== 'DRAFT') {
            return back()->with('error', 'Hanya Supplier Payment DRAFT yang dapat diposting.');
        }

        DB::transaction(function() use ($payment) {
            $payment->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($payment->allocations as $alloc) {
                $ap = $alloc->accountPayable;
                $ap->increment('paid_amount', $alloc->allocated_amount);
                $ap->decrement('remaining_amount', $alloc->allocated_amount);

                if ($ap->remaining_amount <= 0) {
                    $ap->update(['status' => 'PAID']);
                } else {
                    $ap->update(['status' => 'PARTIAL']);
                }

                // Update original invoice paid_amount if applicable
                if ($ap->invoice) {
                    $ap->invoice->increment('paid_amount', $alloc->allocated_amount);
                    if ($ap->invoice->paid_amount >= $ap->invoice->total_amount) {
                        $ap->invoice->update(['status' => 'PAID']);
                    } else {
                        $ap->invoice->update(['status' => 'PARTIAL']);
                    }
                }
            }
        });

        return back()->with('success', 'Supplier Payment berhasil diposting. Utang usaha (AP) terbayar.');
    }

    // Purchase Return Actions
    public function storeReturn(Request $request): RedirectResponse
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.return_qty' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $latest = PurchaseReturn::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $returnNumber = 'PRT-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += ($item['return_qty'] * $item['unit_cost']);
            }

            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'goods_receipt_id' => $request->goods_receipt_id,
                'supplier_id' => $request->supplier_id,
                'return_date' => $request->return_date,
                'status' => 'DRAFT',
                'total_amount' => $totalAmount,
                'reason' => $request->reason,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'goods_receipt_item_id' => $item['goods_receipt_item_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'return_qty' => $item['return_qty'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['return_qty'] * $item['unit_cost'],
                ]);
            }
        });

        return back()->with('success', 'Purchase Return berhasil dibuat.');
    }

    public function postReturn(int $id): RedirectResponse
    {
        $return = PurchaseReturn::findOrFail($id);
        if ($return->status !== 'DRAFT') {
            return back()->with('error', 'Hanya Purchase Return DRAFT yang dapat diposting.');
        }

        DB::transaction(function() use ($return) {
            $return->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            // Deduct from inventory batches
            foreach ($return->items as $item) {
                $grItem = $item->goodsReceiptItem;
                if ($grItem && $grItem->inventoryBatch) {
                    $grItem->inventoryBatch->decrement('quantity', $item->return_qty);
                }
            }
        });

        return back()->with('success', 'Purchase Return berhasil diposting. Stok inventaris disesuaikan.');
    }

    // Landed Cost Actions
    public function storeLandedCost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'cost_type' => 'required|in:FREIGHT,INSURANCE,CUSTOMS,OTHER',
            'amount' => 'required|numeric|min:0.01',
            'allocation_method' => 'required|in:BY_QTY,BY_VALUE,EVEN',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;

        LandedCost::create($validated);

        return back()->with('success', 'Landed Cost berhasil ditambahkan.');
    }

    // Supplier Performance Actions
    public function storeSupplierPerformance(Request $request, int $supplier_id): RedirectResponse
    {
        $validated = $request->validate([
            'evaluation_period' => 'required|date',
            'on_time_delivery_score' => 'required|numeric|min:0|max:100',
            'price_score' => 'required|numeric|min:0|max:100',
            'quality_score' => 'required|numeric|min:0|max:100',
            'service_score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $overall = ($validated['on_time_delivery_score'] + $validated['price_score'] + $validated['quality_score'] + $validated['service_score']) / 4;

        SupplierPerformance::create([
            'supplier_id' => $supplier_id,
            'evaluation_period' => $validated['evaluation_period'],
            'on_time_delivery_score' => $validated['on_time_delivery_score'],
            'price_score' => $validated['price_score'],
            'quality_score' => $validated['quality_score'],
            'service_score' => $validated['service_score'],
            'overall_score' => $overall,
            'notes' => $validated['notes'],
            'evaluated_by' => auth()->id() ?? 1,
        ]);

        return back()->with('success', 'Evaluasi Performa Supplier berhasil disimpan.');
    }
}
