<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\MasterData\BankAccount;
use App\Models\MasterData\Customer;
use App\Models\POS\Cart;
use App\Models\POS\CartVoidLog;
use App\Models\POS\HeldCart;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory as Category;
use App\Repositories\Contracts\Inventory\BalanceRepositoryInterface;
use App\Services\Inventory\StockMovementService;
use App\Services\POS\CartService;
use App\Services\Pricing\PricingService;
use App\Enums\Inventory\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Accounting\PaymentMethod;          // ← GANTI BankAccount
use App\Services\Accounting\JournalService;       // ← BARU


class PosTransactionController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PricingService $pricingService,
        private readonly BalanceRepositoryInterface $balanceRepository,
        private readonly StockMovementService $stockMovement,
        private readonly JournalService $journalService,
    ) {}

    // ═══════════════════════════════════════════════════════════
    // RENDER HALAMAN POS UTAMA
    // ═══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $user = Auth::user();
        $locationId = $this->getCurrentLocationId($user);

        // ───────────────────────────────────────────────────────
        // 1. AMBIL PRODUCTS
        //    Subquery disesuaikan dengan struktur DB Anda:
        //    - product_barcodes → FK ke product_variant_id (JOIN ke product_variants)
        //    - price_list_items → FK ke product_variant_id (JOIN ke product_variants)
        //    - product_images   → kolom image_path (bukan image)
        // ───────────────────────────────────────────────────────
        $products = Product::where('is_active', true)
            ->with(['category', 'variants'])
            ->select('id', 'product_name', 'product_code', 'category_id')
            // ✅ BARCODE: via product_variants (FK product_variant_id)
            ->addSelect(['barcode' => function ($query) {
                $query->select('pb.barcode')
                    ->from('product_barcodes as pb')
                    ->join('product_variants as pv', 'pv.id', '=', 'pb.product_variant_id')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->orderBy('pb.is_primary', 'desc')
                    ->limit(1);
            }])

            // ✅ SELL PRICE: via product_variants (FK product_variant_id)
            //    Ambil harga dengan min_qty terkecil (harga satuan)
            ->addSelect(['sell_price' => function ($query) {
                $query->select('pli.price')
                    ->from('price_list_items as pli')
                    ->join('product_variants as pv', 'pv.id', '=', 'pli.product_variant_id')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->orderBy('pli.min_qty', 'asc')
                    ->limit(1);
            }])

            // ✅ IMAGE: kolom image_path (bukan 'image')
            ->addSelect(['image' => function ($query) {
                $query->select('image_path')
                    ->from('product_images')
                    ->whereColumn('product_images.product_id', 'products.id')
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort_order', 'asc')
                    ->limit(1);
            }])
            ->get();

        // ───────────────────────────────────────────────────────
        // 2. AMBIL STOK DARI INVENTORY BALANCES
        // ───────────────────────────────────────────────────────
        $stockPerProduct = $this->getStockPerProduct($products, $locationId);

        // ───────────────────────────────────────────────────────
        // 3. FORMAT PRODUCTS UNTUK FRONTEND
        // ───────────────────────────────────────────────────────
        $productsFormatted = $products->map(function (Product $product) use ($stockPerProduct) {
            return [
                'id'            => $product->id,
                'title'         => $product->product_name,
                'sku'           => $product->product_code,
                'barcode'       => $product->barcode,
                'sell_price'    => (float) ($product->sell_price ?? 0),
                'image'         => $product->image,
                'stock'         => $stockPerProduct[$product->id] ?? 0,
                'category_id'   => $product->category_id,
                'category_name' => $product->category?->name,
                'has_variants'  => $product->variants->isNotEmpty(),
            ];
        });

        // ───────────────────────────────────────────────────────
        // 4. CATEGORIES
        // ───────────────────────────────────────────────────────
        $categories = Category::select('id', 'category_name')->get();

        // ───────────────────────────────────────────────────────
        // 5. CUSTOMERS (dengan eager load loyalty account)
        // ───────────────────────────────────────────────────────
        $customers = Customer::select(
            'id',
            'customer_code',
            'customer_name',
            'customer_category_id',
            'phone',
            'email',
            'credit_limit'
        )
            ->where('is_active', true)
            ->with(['loyaltyAccount.tier'])
            ->limit(100)
            ->get()
            ->map(function ($customer) {
                return [
                    'id'                    => $customer->id,
                    'code'                  => $customer->customer_code,
                    'name'                  => $customer->customer_name,
                    'phone'                 => $customer->phone,
                    'email'                 => $customer->email,
                    'credit_limit'          => (float) $customer->credit_limit,
                    'customer_category_id'  => $customer->customer_category_id,
                    'is_loyalty_member'     => $customer->loyaltyAccount !== null,
                    'loyalty_account'       => $customer->loyaltyAccount ? [
                        'account_no'        => $customer->loyaltyAccount->account_no,
                        'current_balance'   => $customer->loyaltyAccount->current_balance,
                        'membership_tier'   => $customer->loyaltyAccount->tier?->tier_code,
                        'tier_name'         => $customer->loyaltyAccount->tier?->tier_name,
                        'lifetime_spending' => (float) $customer->loyaltyAccount->lifetime_spending,
                        'point_expiry_date' => $customer->loyaltyAccount->point_expiry_date?->format('Y-m-d'),
                    ] : null,
                ];
            });


        // ───────────────────────────────────────────────────────
        // 6. CART USER SAAT INI
        // ───────────────────────────────────────────────────────
        $carts = Cart::where('user_id', $user->id)
            ->with('product')
            ->get()
            ->map(fn(Cart $cart) => [
                'id'         => $cart->id,
                'product_id' => $cart->product_id,
                'qty'        => $cart->qty,
                'price'      => $cart->sell_price,
                'product'    => [
                    'id'         => $cart->product->id,
                    'title'      => $cart->product->product_name,
                    'image'      => $cart->product->images()
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort_order', 'asc')
                        ->value('image_path'),
                    'sell_price' => $this->getProductSellPrice($cart->product_id),
                ],
            ]);

        $cartsTotal = $carts->sum(fn($c) => $c['price'] * $c['qty']);

        // ───────────────────────────────────────────────────────
        // 7. HELD CARTS
        // ───────────────────────────────────────────────────────
        $heldCarts = HeldCart::where('user_id', $user->id)
            ->with('items.product')
            ->get()
            ->map(fn(HeldCart $held) => [
                'id'          => $held->id,
                'label'       => $held->label,
                'total'       => $held->items->sum(fn($i) => $i->price * $i->qty),
                'items_count' => $held->items->count(),
                'created_at'  => $held->created_at,
            ]);

        // ───────────────────────────────────────────────────────
        // 8. INITIAL PRICING PREVIEW
        // ───────────────────────────────────────────────────────
        $initialPricingPreview = $this->pricingService->calculatePreview(
            $carts,
            null,
            0,
            0,
            0,
            null
        );

        // ───────────────────────────────────────────────────────
        // 7. PAYMENT METHODS (✅ FIXED - pakai PaymentMethod)
        // ───────────────────────────────────────────────────────
        $paymentMethods = PaymentMethod::with('account')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($pm) => [
                'id'           => $pm->id,
                'value'        => $pm->method_type->value,
                'code'         => $pm->method_code,
                'label'        => $pm->method_name,
                'description'  => 'Akun: ' . $pm->account?->account_name,
                'account_id'   => $pm->account_id,
                'account_name' => $pm->account?->account_name,
                'is_cash'      => $pm->isCash(),
            ]);

        // Pisahkan untuk compatibility dengan frontend lama
        $paymentGateways = $paymentMethods->map(fn($pm) => [
            'value' => $pm['value'],
            'label' => $pm['label'],
        ])->unique('value')->values()->toArray();

        $bankAccounts = $paymentMethods->filter(fn($pm) => $pm['value'] === 'TRANSFER')->values();

        // ───────────────────────────────────────────────────────
        // 10. RENDER INERTIA
        // ───────────────────────────────────────────────────────
        return Inertia::render('POS/Index', [
            'carts'                   => $carts,
            'carts_total'             => $cartsTotal,
            'heldCarts'               => $heldCarts,
            'customers'               => $customers,
            'products'                => $productsFormatted,
            'categories'              => $categories,
            'initialPricingPreview'   => $initialPricingPreview,
            'paymentGateways'         => $paymentGateways,
            'paymentMethods'          => $paymentMethods,
            'defaultPaymentGateway'   => 'cash',
            'bankAccounts'            => $bankAccounts,
            'loyaltyTierOptions'      => config('pos.loyalty_tiers', []),
            'currentLocationId'       => $locationId,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // TAMBAH PRODUK KE CART
    // ═══════════════════════════════════════════════════════════
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'sell_price' => 'required|numeric|min:0',
            'qty'        => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $locationId = $this->getCurrentLocationId($user);

        $this->validateStockAvailability(
            productId: $request->product_id,
            requestedQty: $request->qty,
            locationId: $locationId,
            userId: $user->id
        );

        $this->cartService->addToCart(
            $user->id,
            $request->product_id,
            $request->qty,
            $request->sell_price
        );

        return back()->with('success', 'Produk ditambahkan');
    }

    // ═══════════════════════════════════════════════════════════
    // UPDATE QUANTITY CART
    // ═══════════════════════════════════════════════════════════
    public function updateCart(Request $request, int $cartId)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $locationId = $this->getCurrentLocationId($user);

        $cart = Cart::where('id', $cartId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $deltaQty = $request->qty - $cart->qty;

        if ($deltaQty > 0) {
            $this->validateStockAvailability(
                productId: $cart->product_id,
                requestedQty: $deltaQty,
                locationId: $locationId,
                userId: $user->id
            );
        }

        $this->cartService->updateQty($user->id, $cartId, $request->qty);

        return back();
    }

    // ═══════════════════════════════════════════════════════════
    // HAPUS ITEM DARI CART
    // ═══════════════════════════════════════════════════════════
    public function destroyCart(Request $request, int $cartId)
    {
        $user = Auth::user();

        if ($request->void_reason) {
            $cart = Cart::where('id', $cartId)
                ->where('user_id', $user->id)
                ->first();

            CartVoidLog::create([
                'user_id'    => $user->id,
                'cart_id'    => $cartId,
                'product_id' => $cart?->product_id,
                'qty'        => $cart?->qty ?? 0,
                'reason'     => $request->void_reason,
                'voided_at'  => now(),
            ]);
        }

        $this->cartService->removeFromCart($user->id, $cartId);

        return back();
    }

    // ═══════════════════════════════════════════════════════════
    // HOLD / RECALL / CLEAR CART
    // ═══════════════════════════════════════════════════════════
    public function hold(Request $request)
    {
        $user = Auth::user();
        $this->cartService->holdCart($user->id, $request->label);
        return back()->with('success', 'Transaksi ditahan');
    }

    public function recallHold(int $heldCartId)
    {
        $user = Auth::user();
        $this->cartService->recallHeldCart($user->id, $heldCartId);
        return back()->with('success', 'Transaksi dipulihkan');
    }

    public function clearCart()
    {
        $user = Auth::user();
        $this->cartService->clearCart($user->id);
        return back();
    }

    // ═══════════════════════════════════════════════════════════
    // PRICING PREVIEW (AJAX)
    // ═══════════════════════════════════════════════════════════
    public function pricingPreview(Request $request)
    {
        $user = Auth::user();

        $carts = Cart::where('user_id', $user->id)
            ->with('product')
            ->get();

        $preview = $this->pricingService->calculatePreview(
            $carts,
            $request->customer_id,
            $request->discount ?? 0,
            $request->shipping_cost ?? 0,
            $request->redeem_points ?? 0,
            $request->customer_voucher_id
        );

        return response()->json(['data' => $preview]);
    }

    // ═══════════════════════════════════════════════════════════
    // SUBMIT TRANSAKSI
    // ═══════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'              => 'required|exists:customers,id',
            'grand_total'              => 'required|numeric|min:0',
            'cash'                     => 'required_if:pay_later,false|numeric|min:0',
            'payment_gateway'          => 'nullable|string',
            'payment_method_id'        => 'nullable|integer|exists:payment_methods,id',  // ← BARU
            'pay_later'                => 'boolean',
            'due_date'                 => 'required_if:pay_later,true|date|after:today',
            'payment_splits'           => 'nullable|array',
            'payment_splits.*.method'  => 'required_with:payment_splits|string',
            'payment_splits.*.amount'  => 'required_with:payment_splits|numeric|min:0',
            'payment_splits.*.payment_method_id' => 'nullable|integer|exists:payment_methods,id',
            'location_id'              => 'nullable|integer|exists:inventory_locations,id',
            'redeem_points'            => 'nullable|integer|min:0',
            'earn_points'              => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $locationId = $request->location_id ?? $this->getCurrentLocationId($user);

        // Validasi stock
        $carts = Cart::where('user_id', $user->id)->with('product')->get();
        foreach ($carts as $cart) {
            $availableStock = $this->getProductStock($cart->product_id, $locationId);
            if ($availableStock < $cart->qty) {
                return back()->withErrors([
                    'stock' => "Stok {$cart->product->product_name} tidak mencukupi"
                ]);
            }
        }

        // Validasi redeem points
        $redeemPointsValue = 0;
        if ($request->redeem_points > 0) {
            $account = app(\App\Repositories\Contracts\Loyalty\AccountRepositoryInterface::class)
                ->findByCustomerId($request->customer_id);

            if (!$account || $account->current_balance < $request->redeem_points) {
                return back()->withErrors([
                    'redeem_points' => 'Poin loyalty tidak mencukupi'
                ]);
            }

            $config = \App\Models\Loyalty\LoyaltyConfiguration::getInstance();
            $redeemPointsValue = $request->redeem_points * $config->point_value;
        }

        $transaction = DB::transaction(function () use ($user, $request, $locationId, $carts, $redeemPointsValue) {
            // 1. Buat transaksi
            $transaction = $this->cartService->checkout($user->id, $request->all());

            // 2. Kurangi stock
            foreach ($carts as $cart) {
                $this->decreaseStockOnSale(
                    productId: $cart->product_id,
                    qty: $cart->qty,
                    locationId: $locationId,
                    reference: $transaction,
                    userId: $user->id
                );
            }

            // 3. ✅ AUTO JOURNAL - Payment entry
            $paymentMethodsData = $this->buildPaymentMethodsArray($request, $transaction);

            if (!empty($paymentMethodsData)) {
                $this->journalService->createPosTransactionJournal(
                    $transaction,
                    $paymentMethodsData
                );
            }

            // 4. REDEEM POINTS
            if ($request->redeem_points > 0) {
                $loyaltyService = app(\App\Services\Loyalty\LoyaltyService::class);
                $loyaltyService->redeemPointsAsPayment(
                    customerId: $request->customer_id,
                    pointsToRedeem: $request->redeem_points,
                    reference: $transaction,
                    userId: $user->id
                );
            }

            // 5. EARN POINTS
            if ($request->earn_points !== false) {
                $loyaltyService = app(\App\Services\Loyalty\LoyaltyService::class);
                $earnResult = $loyaltyService->earnPoints(
                    customerId: $request->customer_id,
                    transactionValue: $request->grand_total,
                    reference: $transaction,
                    userId: $user->id
                );
                session()->flash('earned_points', $earnResult['earned']);
            }

            return $transaction;
        });

        return redirect()
            ->route('pos.sales.show', $transaction->id)
            ->with(
                'success',
                'Transaksi berhasil!' .
                    (session('earned_points') ? ' +' . session('earned_points') . ' poin' : '')
            );
    }

    /**
     * Helper: Build array payment methods untuk auto journal
     */
    private function buildPaymentMethodsArray($request, $transaction): array
    {
        $payments = [];

        // Split payment
        if (!empty($request->payment_splits)) {
            foreach ($request->payment_splits as $split) {
                $pmId = $split['payment_method_id'] ?? null;
                $pm = $pmId ? PaymentMethod::with('account')->find($pmId) : null;

                $payments[] = [
                    'method_name'  => $pm?->method_name ?? $split['method'],
                    'account_id'   => $pm?->account_id ?? $this->getDefaultAccountIdForType($split['method']),
                    'amount'       => (float) $split['amount'],
                ];
            }
        }
        // Single payment
        elseif (!$request->pay_later) {
            $pmId = $request->payment_method_id ?? null;
            $pm = $pmId ? PaymentMethod::with('account')->find($pmId) : null;

            // Fallback: cari by method_type
            if (!$pm && $request->payment_gateway) {
                $pm = PaymentMethod::with('account')
                    ->where('method_type', strtoupper($request->payment_gateway))
                    ->where('is_active', true)
                    ->first();
            }

            $payments[] = [
                'method_name'  => $pm?->method_name ?? ($request->payment_gateway ?? 'Cash'),
                'account_id'   => $pm?->account_id ?? $this->getDefaultAccountIdForType('CASH'),
                'amount'       => (float) ($request->cash ?? $transaction->grand_total),
            ];
        }

        return $payments;
    }

    private function getDefaultAccountIdForType(string $type): int
    {
        $pm = PaymentMethod::with('account')
            ->where('method_type', strtoupper($type))
            ->where('is_active', true)
            ->first();

        if (!$pm) {
            throw new \DomainException("Payment method type {$type} tidak ditemukan");
        }

        return $pm->account_id;
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    private function getCurrentLocationId($user): ?int
    {
        if ($locationId = session('pos_location_id')) {
            return (int) $locationId;
        }

        if ($locationId = config('pos.default_location_id')) {
            return (int) $locationId;
        }

        $shift = \App\Models\POS\CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($shift && $shift->location_id) {
            return (int) $shift->location_id;
        }

        if (isset($user->default_location_id)) {
            return (int) $user->default_location_id;
        }

        return \App\Models\Inventory\InventoryLocation::where('is_active', true)
            ->whereIn('type', ['STORE_WAREHOUSE', 'WAREHOUSE'])
            ->value('id');
    }

    private function getStockPerProduct($products, ?int $locationId): array
    {
        $variantIds = $products->flatMap(fn($p) => $p->variants->pluck('id'))->unique()->values();

        if ($variantIds->isEmpty()) {
            return [];
        }

        $query = InventoryBalance::whereIn('product_variant_id', $variantIds)
            ->with('variant:id,product_id');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $balances = $query->get();

        $stockMap = [];
        foreach ($balances as $balance) {
            $productId = $balance->variant?->product_id;
            if ($productId) {
                $stockMap[$productId] = ($stockMap[$productId] ?? 0) + (float) $balance->qty_available;
            }
        }

        return $stockMap;
    }

    private function getProductStock(int $productId, ?int $locationId): float
    {
        $query = InventoryBalance::whereHas('variant', fn($q) => $q->where('product_id', $productId));

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return (float) $query->sum('qty_available');
    }

    private function validateStockAvailability(
        int $productId,
        int $requestedQty,
        ?int $locationId,
        int $userId
    ): void {
        $available = $this->getProductStock($productId, $locationId);

        $inCart = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->sum('qty');

        $totalNeeded = $inCart + $requestedQty;

        if ($available < $totalNeeded) {
            $product = Product::find($productId);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'stock' => "Stok {$product?->product_name} tidak mencukupi. Tersedia: {$available}, dibutuhkan: {$totalNeeded}"
            ]);
        }
    }

    /**
     * ✅ Helper baru: Ambil harga jual dari price_list_items via variant
     */
    private function getProductSellPrice(int $productId): float
    {
        return (float) DB::table('price_list_items as pli')
            ->join('product_variants as pv', 'pv.id', '=', 'pli.product_variant_id')
            ->where('pv.product_id', $productId)
            ->orderBy('pli.min_qty', 'asc')
            ->value('pli.price') ?? 0;
    }

    private function decreaseStockOnSale(
        int $productId,
        float $qty,
        ?int $locationId,
        $reference,
        int $userId
    ): void {
        if (!$locationId) {
            return;
        }

        $variant = \App\Models\Product\ProductVariant::where('product_id', $productId)
            ->where('is_active', true)
            ->first();

        if (!$variant) {
            return;
        }

        $balance = InventoryBalance::where('product_variant_id', $variant->id)
            ->where('location_id', $locationId)
            ->first();

        if (!$balance || $balance->qty_available < $qty) {
            \Log::warning("Stock tidak cukup untuk variant {$variant->id} di location {$locationId}");
            return;
        }

        $batch = \App\Models\Inventory\InventoryBatch::where('product_variant_id', $variant->id)
            ->where('location_id', $locationId)
            ->where('is_active', true)
            ->first();

        $this->stockMovement->recordMovement(
            type: TransactionType::SALE,
            variantId: $variant->id,
            locationId: $locationId,
            qty: $qty,
            unitCost: $batch?->unit_cost ?? 0,
            batchId: $batch?->id,
            reference: $reference,
            notes: "Penjualan POS",
            userId: $userId,
        );
    }
}
