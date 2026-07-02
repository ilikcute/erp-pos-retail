<?php

namespace App\Http\Controllers\POS;

use App\Enums\Inventory\TransactionType;
use App\Events\TransactionCreated;
use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentMethod;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\InventoryLocation;
use App\Models\Loyalty\LoyaltyConfiguration;
use App\Models\MasterData\Customer;
use App\Models\POS\Cart;
use App\Models\POS\CartVoidLog;
use App\Models\POS\CashierSession;
use App\Models\POS\HeldCart;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory as Category;
use App\Models\Product\ProductVariant;
use App\Repositories\Contracts\Inventory\BalanceRepositoryInterface;
use App\Repositories\Contracts\Loyalty\AccountRepositoryInterface;
use App\Services\Accounting\JournalService;
use App\Services\Inventory\StockMovementService;
use App\Services\Loyalty\LoyaltyService;
use App\Services\POS\CartService;
use App\Services\POS\DayClosingService;
use App\Services\POS\MonthClosingService;
use App\Services\POS\SessionService;
use App\Services\Pricing\PricingService;
use App\Services\Promotion\PromotionService;
use App\Support\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PosTransactionController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PricingService $pricingService,
        private readonly BalanceRepositoryInterface $balanceRepository,
        private readonly StockMovementService $stockMovement,
        private readonly JournalService $journalService,
        private readonly LoyaltyService $loyaltyService,
        private readonly PromotionService $promotionService,
        private readonly AccountRepositoryInterface $loyaltyAccountRepo,
        private readonly AuditService $auditService,
    ) {}

    // ═══════════════════════════════════════════════════════════
    // 1. RENDER HALAMAN POS UTAMA
    // ═══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $user = Auth::user();
        $locationId = $this->getCurrentLocationId($user);

        // ───────────────────────────────────────────────────────
        // 1.1 PRODUCTS (dengan subquery)
        // ───────────────────────────────────────────────────────
        $products = Product::where('is_active', true)
            ->with(['category', 'variants.barcodes'])
            ->select('id', 'product_name', 'product_code', 'category_id')
            ->addSelect(['barcode' => function ($query) {
                $query->select('pb.barcode')
                    ->from('product_barcodes as pb')
                    ->join('product_variants as pv', 'pv.id', '=', 'pb.product_variant_id')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->orderBy('pb.is_primary', 'desc')
                    ->limit(1);
            }])
            ->addSelect(['sell_price' => function ($query) {
                $query->select('pli.price')
                    ->from('price_list_items as pli')
                    ->join('product_variants as pv', 'pv.id', '=', 'pli.product_variant_id')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->orderBy('pli.min_qty', 'asc')
                    ->limit(1);
            }])
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
        // 1.2 STOCK dari inventory balances
        // ───────────────────────────────────────────────────────
        $stockPerProduct = $this->getStockPerProduct($products, $locationId);

        $productsFormatted = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'title' => $product->product_name,
            'sku' => $product->product_code,
            'barcode' => $product->barcode,
            'sell_price' => (float) ($product->sell_price ?? 0),
            'image' => $product->image,
            'stock' => $stockPerProduct[$product->id] ?? 0,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'has_variants' => $product->variants->isNotEmpty(),
            'variant_skus' => $product->variants->pluck('sku')->filter()->values()->toArray(),
            'variant_barcodes' => $product->variants->flatMap(fn ($v) => $v->barcodes->pluck('barcode'))->filter()->unique()->values()->toArray(),
        ]);

        // ───────────────────────────────────────────────────────
        // 1.3 CATEGORIES
        // ───────────────────────────────────────────────────────
        $categories = Category::select('id', 'category_name')->get();

        // ───────────────────────────────────────────────────────
        // 1.4 CUSTOMERS (dengan loyalty eager load)
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
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'code' => $customer->customer_code,
                'name' => $customer->customer_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'credit_limit' => (float) $customer->credit_limit,
                'customer_category_id' => $customer->customer_category_id,
                'is_loyalty_member' => $customer->loyaltyAccount !== null,
                'loyalty_account' => $customer->loyaltyAccount ? [
                    'account_no' => $customer->loyaltyAccount->account_no,
                    'current_balance' => $customer->loyaltyAccount->current_balance,
                    'membership_tier' => $customer->loyaltyAccount->tier?->tier_code,
                    'tier_name' => $customer->loyaltyAccount->tier?->tier_name,
                    'lifetime_spending' => (float) $customer->loyaltyAccount->lifetime_spending,
                    'point_expiry_date' => $customer->loyaltyAccount->point_expiry_date?->format('Y-m-d'),
                ] : null,
            ]);

        // ───────────────────────────────────────────────────────
        // 1.5 CART (✅ FIXED: eager load untuk hindari N+1)
        // ───────────────────────────────────────────────────────
        $cartsCollection = Cart::where('user_id', $user->id)
            ->with([
                'product.images',
                'product.priceListItems',
                'product.variants',
                'product.tax',
            ])
            ->get();

        $carts = $cartsCollection->map(fn (Cart $cart) => [
            'id' => $cart->id,
            'product_id' => $cart->product_id,
            'qty' => $cart->qty,
            'price' => $cart->sell_price,
            'product' => [
                'id' => $cart->product->id,
                'title' => $cart->product->product_name,
                'product_name' => $cart->product->product_name,
                'product_code' => $cart->product->product_code,
                'sku' => $cart->product->product_code,
                'image' => $cart->product->images
                    ->sortByDesc('is_primary')
                    ->sortBy('sort_order')
                    ->first()?->image_path,
                'sell_price' => $cart->product->priceListItems
                    ->sortBy('min_qty')
                    ->first()?->price ?? $cart->sell_price,
            ],
        ]);

        $cartsTotal = $carts->sum(fn ($c) => $c['price'] * $c['qty']);

        // ───────────────────────────────────────────────────────
        // 1.6 HELD CARTS
        // ───────────────────────────────────────────────────────
        $heldCarts = HeldCart::where('user_id', $user->id)
            ->with('items.product')
            ->get()
            ->map(fn (HeldCart $held) => [
                'id' => $held->id,
                'label' => $held->label,
                'total' => $held->items->sum(fn ($i) => $i->price * $i->qty),
                'items_count' => $held->items->count(),
                'created_at' => $held->created_at,
            ]);

        // ───────────────────────────────────────────────────────
        // 1.7 INITIAL PRICING PREVIEW
        // ───────────────────────────────────────────────────────
        $initialPricingPreview = $this->pricingService->calculatePreview(
            $cartsCollection,
            null,
            0,
            0,
            0,
            null
        );

        // ───────────────────────────────────────────────────────
        // 1.8 PAYMENT METHODS (dari Accounting module)
        // ───────────────────────────────────────────────────────
        $paymentMethods = PaymentMethod::with('account')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($pm) => [
                'id' => $pm->id,
                'value' => $pm->method_type->value,
                'code' => $pm->method_code,
                'label' => $pm->method_name,
                'description' => 'Akun: '.$pm->account?->account_name,
                'account_id' => $pm->account_id,
                'account_name' => $pm->account?->account_name,
                'is_cash' => $pm->isCash(),
            ]);

        $paymentGateways = $paymentMethods
            ->map(fn ($pm) => ['value' => $pm['value'], 'label' => $pm['label']])
            ->unique('value')
            ->values()
            ->toArray();

        $bankAccounts = $paymentMethods
            ->filter(fn ($pm) => $pm['value'] === 'TRANSFER')
            ->values();

        // ───────────────────────────────────────────────────────
        // 1.9 RENDER
        // ───────────────────────────────────────────────────────
        return Inertia::render('POS/Index', [
            'carts' => $carts,
            'carts_total' => $cartsTotal,
            'heldCarts' => $heldCarts,
            'customers' => $customers,
            'products' => $productsFormatted,
            'categories' => $categories,
            'initialPricingPreview' => $initialPricingPreview,
            'paymentGateways' => $paymentGateways,
            'paymentMethods' => $paymentMethods,
            'defaultPaymentGateway' => 'cash',
            'bankAccounts' => $bankAccounts,
            'loyaltyTierOptions' => config('pos.loyalty_tiers', []),
            'currentLocationId' => $locationId,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // 2. CART OPERATIONS
    // ═══════════════════════════════════════════════════════════
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'sell_price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:1',
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

    public function updateCart(Request $request, int $cartId)
    {
        $request->validate(['qty' => 'required|integer|min:1']);

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

    public function destroyCart(Request $request, int $cartId)
    {
        $user = Auth::user();

        if ($request->void_reason) {
            $cart = Cart::where('id', $cartId)
                ->where('user_id', $user->id)
                ->first();

            CartVoidLog::create([
                'user_id' => $user->id,
                'cart_id' => $cartId,
                'product_id' => $cart?->product_id,
                'qty' => $cart?->qty ?? 0,
                'reason' => $request->void_reason,
                'voided_at' => now(),
            ]);
        }

        $this->cartService->removeFromCart($user->id, $cartId);

        return back();
    }

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
    // 3. PRICING PREVIEW (AJAX)
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
    // 4. SUBMIT TRANSAKSI (✅ FIXED: validasi lengkap + error handling)
    // ═══════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'grand_total' => 'required|numeric|min:0',
            'cash' => 'required_if:pay_later,false|numeric|min:0',
            'payment_gateway' => 'nullable|string',
            'payment_method_id' => 'nullable|integer|exists:payment_methods,id',
            'pay_later' => 'boolean',
            'due_date' => 'nullable|required_if:pay_later,true|date|after:today',
            'payment_splits' => 'nullable|array',
            'payment_splits.*.method' => 'required_with:payment_splits|string',
            'payment_splits.*.amount' => 'required_with:payment_splits|numeric|min:0',
            'payment_splits.*.payment_method_id' => 'nullable|integer|exists:payment_methods,id',
            'location_id' => 'nullable|integer|exists:inventory_locations,id',
            'redeem_points' => 'nullable|integer|min:0',
            'earn_points' => 'nullable|boolean',
            'applied_promotions' => 'nullable|array',
        ]);

        $user = Auth::user();
        $locationId = $request->location_id ?? $this->getCurrentLocationId($user);
        $transactionDate = now();

        // ═══════════════════════════════════════════════════════════
        // ✅ VALIDASI 1: Cek apakah hari sudah ditutup
        // ═══════════════════════════════════════════════════════════
        $dayClosingService = app(DayClosingService::class);
        $dayCheck = $dayClosingService->canTransactOnDate(
            $transactionDate->toDateString(),
            $locationId
        );

        if (! $dayCheck['allowed']) {
            return back()->withErrors([
                'transaction' => $dayCheck['reason'],
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // ✅ VALIDASI 2: Cek apakah bulan sudah dikunci
        // ═══════════════════════════════════════════════════════════
        $monthClosingService = app(MonthClosingService::class);
        $monthCheck = $monthClosingService->canTransactInPeriod(
            $transactionDate->year,
            $transactionDate->month,
            $locationId
        );

        if (! $monthCheck['allowed']) {
            return back()->withErrors([
                'transaction' => $monthCheck['reason'],
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // ✅ VALIDASI 3: Cek sesi aktif
        // ═══════════════════════════════════════════════════════════
        $activeSession = session('pos_session_id')
            ? CashierSession::find(session('pos_session_id'))
            : CashierSession::where('user_id', $user->id)->where('status', 'OPEN')->first();

        if (! $activeSession || $activeSession->isClosed()) {
            return back()->withErrors([
                'session' => 'Sesi kasir belum dibuka atau sudah ditutup. Silakan buka sesi terlebih dahulu.',
            ]);
        }

        // ───────────────────────────────────────────────────────
        // 4.1 VALIDASI STOCK
        // ───────────────────────────────────────────────────────
        $carts = Cart::where('user_id', $user->id)
            ->with(['product.tax', 'product.variants', 'product.priceListItems'])
            ->get();
        foreach ($carts as $cart) {
            $availableStock = $this->getProductStock($cart->product_id, $locationId);
            if ($availableStock < $cart->qty) {
                $msg = $availableStock == 0
                    ? "Stok barang dagangan \"{$cart->product->product_name}\" belum tercatat di lokasi ini (qty tersedia: 0). Silakan lakukan stok opname atau tambah penerimaan barang terlebih dahulu."
                    : "Stok \"{$cart->product->product_name}\" tidak mencukupi. Tersedia: {$availableStock}, dibutuhkan: {$cart->qty}";

                return back()->withErrors(['stock' => $msg]);
            }
        }

        // ───────────────────────────────────────────────────────
        // 4.2 VALIDASI REDEEM POINTS
        // ───────────────────────────────────────────────────────
        $redeemPointsValue = 0;
        if ($request->redeem_points > 0) {
            $account = $this->loyaltyAccountRepo->findByCustomerId($request->customer_id);

            if (! $account || $account->current_balance < $request->redeem_points) {
                return back()->withErrors([
                    'redeem_points' => 'Poin loyalty tidak mencukupi',
                ]);
            }

            $config = LoyaltyConfiguration::getInstance();
            $redeemPointsValue = $request->redeem_points * $config->point_value;
        }

        // ───────────────────────────────────────────────────────
        // 4.3 ✅ VALIDASI SPLIT PAYMENT TOTAL
        // ───────────────────────────────────────────────────────
        if (! empty($request->payment_splits) && ! $request->pay_later) {
            $splitTotal = collect($request->payment_splits)->sum('amount');
            $expectedTotal = $request->grand_total - $redeemPointsValue;

            if (abs($splitTotal - $expectedTotal) > 0.01) {
                return back()->withErrors([
                    'payment_splits' => 'Total split payment (Rp '.
                        number_format($splitTotal, 0, ',', '.').
                        ') tidak sama dengan total bayar (Rp '.
                        number_format($expectedTotal, 0, ',', '.').')',
                ]);
            }
        }

        // ───────────────────────────────────────────────────────
        // 4.4 EKSEKUSI TRANSAKSI (dalam 1 transaction besar)
        // ───────────────────────────────────────────────────────
        try {
            $transaction = DB::transaction(function () use ($user, $request, $locationId, $carts, $redeemPointsValue, $activeSession) {
                // Calculate accurate subtotal, discounts, tax, and grand total using PricingService
                $preview = $this->pricingService->calculatePreview(
                    carts: $carts,
                    customerId: $request->customer_id,
                    manualDiscount: (float) ($request->discount ?? 0),
                    shippingCost: (float) ($request->shipping_cost ?? 0),
                    redeemPoints: (float) ($request->redeem_points ?? 0),
                    voucherId: $request->customer_voucher_id
                );

                $payload = $request->all();
                $payload['cashier_session_id'] = $activeSession->id;
                $payload['subtotal'] = $preview['summary']['base_subtotal'];
                $payload['discount'] = $preview['summary']['promo_discount_total'] + $preview['summary']['voucher_discount_total'] + $preview['summary']['loyalty_discount_total'] + $preview['summary']['manual_discount_total'];
                $payload['tax_total'] = $preview['summary']['tax_total'];
                $payload['grand_total'] = $preview['summary']['grand_total'];
                $payload['items_pricing'] = $preview['items'];

                // 1. Buat transaksi
                $transaction = $this->cartService->checkout($user->id, $payload);

                // 2. Kurangi stock (dengan exception jika gagal)
                foreach ($carts as $cart) {
                    $this->decreaseStockOnSale(
                        productId: $cart->product_id,
                        qty: $cart->qty,
                        locationId: $locationId,
                        reference: $transaction,
                        userId: $user->id
                    );
                }

                // 3. ✅ AUTO JOURNAL - Sales entry
                $paymentMethodsData = $this->buildPaymentMethodsArray($request, $transaction, $redeemPointsValue);
                if (! empty($paymentMethodsData)) {
                    $this->journalService->createPosTransactionJournal(
                        $transaction,
                        $paymentMethodsData
                    );
                }

                // 4. ✅ AUTO JOURNAL - COGS entry (HPP)
                $cogsItems = $this->buildCogsItems($carts, $locationId);
                if (! empty($cogsItems)) {
                    $this->journalService->createCogsJournal($transaction, $cogsItems);
                }

                // 5. Log penggunaan promosi
                if (! empty($request->applied_promotions)) {
                    foreach ($request->applied_promotions as $promo) {
                        if (! empty($promo['promotion_id']) && ! empty($promo['discount_amount'])) {
                            $this->promotionService->logUsage(
                                promotionId: $promo['promotion_id'],
                                customerId: $request->customer_id,
                                transactionId: $transaction->id,
                                discountAmount: $promo['discount_amount']
                            );
                        }
                    }
                }

                // 6. Redeem loyalty points
                if ($request->redeem_points > 0) {
                    $this->loyaltyService->redeemPointsAsPayment(
                        customerId: $request->customer_id,
                        pointsToRedeem: $request->redeem_points,
                        reference: $transaction,
                        userId: $user->id
                    );
                }

                // 7. Earn loyalty points
                if ($request->earn_points !== false) {
                    $earnResult = $this->loyaltyService->earnPoints(
                        customerId: $request->customer_id,
                        transactionValue: $request->grand_total,
                        reference: $transaction,
                        userId: $user->id
                    );
                    session()->flash('earned_points', $earnResult['earned']);
                }

                // ✅ BARU: Refresh session stats
                $sessionService = app(SessionService::class);
                $sessionService->refreshSessionStats($activeSession->id);

                // Write Audit Log for POS Transaction creation
                $this->auditService->log(
                    module: 'POS',
                    action: 'POST_SALES_TRANSACTION',
                    tableName: 'sales_transactions',
                    recordId: $transaction->id,
                    documentNo: $transaction->transaction_no,
                    newValues: [
                        'grand_total' => $transaction->grand_total,
                        'paid_amount' => $transaction->paid_amount,
                        'customer_id' => $transaction->customer_id,
                    ],
                );

                return $transaction;
            });

            // Write Activity Log for user completing checkout
            $this->auditService->activity(
                activity: 'POS_CHECKOUT',
                module: 'POS',
                description: "User completed POS checkout for Transaction: {$transaction->transaction_no}"
            );

            event(new TransactionCreated($transaction));

            return back()->with(
                'success',
                'Transaksi berhasil!'.
                    (session('earned_points') ? ' +'.session('earned_points').' poin' : '')
            );
        } catch (\DomainException $e) {
            // ✅ Error handling yang jelas
            return back()->withErrors([
                'transaction' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            \Log::error('POS Transaction failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'transaction' => 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.',
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // 5. HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    private function buildPaymentMethodsArray($request, $transaction, float $redeemPointsValue = 0): array
    {
        $payments = [];

        if (! empty($request->payment_splits)) {
            foreach ($request->payment_splits as $split) {
                $pmId = $split['payment_method_id'] ?? null;
                $pm = $pmId ? PaymentMethod::with('account')->find($pmId) : null;

                if (! $pm && ! empty($split['method'])) {
                    $pm = PaymentMethod::with('account')
                        ->where('method_type', strtoupper($split['method']))
                        ->where('is_active', true)
                        ->first();
                }

                if (! $pm) {
                    throw new \DomainException('Payment method tidak ditemukan: '.($split['method'] ?? 'unknown'));
                }

                $payments[] = [
                    'method_name' => $pm->method_name,
                    'account_id' => $pm->account_id,
                    'amount' => (float) $split['amount'],
                ];
            }
        } elseif (! $request->pay_later) {
            $pmId = $request->payment_method_id ?? null;
            $pm = $pmId ? PaymentMethod::with('account')->find($pmId) : null;

            if (! $pm && $request->payment_gateway) {
                $pm = PaymentMethod::with('account')
                    ->where('method_type', strtoupper($request->payment_gateway))
                    ->where('is_active', true)
                    ->first();
            }

            if (! $pm) {
                // Fallback ke CASH jika tidak ada yang cocok
                $pm = PaymentMethod::with('account')
                    ->where('method_type', 'CASH')
                    ->where('is_active', true)
                    ->first();
            }

            if (! $pm) {
                throw new \DomainException('Payment method CASH tidak ditemukan di sistem');
            }

            $payments[] = [
                'method_name' => $pm->method_name,
                'account_id' => $pm->account_id,
                'amount' => (float) $transaction->grand_total,
            ];
        }

        return $payments;
    }

    /**
     * ✅ BARU: Build items untuk COGS journal
     */
    private function buildCogsItems($carts, ?int $locationId): array
    {
        $items = [];

        foreach ($carts as $cart) {
            $variant = ProductVariant::where('product_id', $cart->product_id)
                ->where('is_active', true)
                ->first();

            if (! $variant) {
                continue;
            }

            $batch = InventoryBatch::where('product_variant_id', $variant->id)
                ->where('location_id', $locationId)
                ->where('is_active', true)
                ->first();

            $items[] = (object) [
                'qty' => $cart->qty,
                'unit_cost' => $batch?->unit_cost ?? 0,
            ];
        }

        return $items;
    }

    private function getCurrentLocationId($user): ?int
    {
        if ($locationId = session('pos_location_id')) {
            return (int) $locationId;
        }

        if ($locationId = config('pos.default_location_id')) {
            return (int) $locationId;
        }

        $session = CashierSession::where('user_id', $user->id)
            ->where('status', 'OPEN')
            ->first();

        if ($session && $session->location_id) {
            return (int) $session->location_id;
        }

        if (isset($user->default_location_id)) {
            return (int) $user->default_location_id;
        }

        return InventoryLocation::where('is_active', true)
            ->whereIn('type', ['STORE_WAREHOUSE', 'WAREHOUSE'])
            ->value('id');
    }

    private function getStockPerProduct($products, ?int $locationId): array
    {
        $variantIds = $products->flatMap(fn ($p) => $p->variants->pluck('id'))->unique()->values();

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
        $query = InventoryBalance::whereHas('variant', fn ($q) => $q->where('product_id', $productId));

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
            throw ValidationException::withMessages([
                'stock' => "Stok {$product?->product_name} tidak mencukupi. Tersedia: {$available}, dibutuhkan: {$totalNeeded}",
            ]);
        }
    }

    /**
     * ✅ FIXED: Throw exception jika stock kurang (bukan silent fail)
     */
    private function decreaseStockOnSale(
        int $productId,
        float $qty,
        ?int $locationId,
        $reference,
        int $userId
    ): void {
        if (! $locationId) {
            throw new \DomainException('Location ID wajib untuk transaksi POS');
        }

        $variant = ProductVariant::where('product_id', $productId)
            ->where('is_active', true)
            ->first();

        if (! $variant) {
            throw new \DomainException("Produk ID {$productId} tidak memiliki variant aktif");
        }

        $balance = InventoryBalance::where('product_variant_id', $variant->id)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            throw new \DomainException('Stok tidak ditemukan untuk produk di lokasi ini');
        }

        if ($balance->qty_available < $qty) {
            throw new \DomainException(
                "Stok tidak cukup. Tersedia: {$balance->qty_available}, diminta: {$qty}"
            );
        }

        $batch = InventoryBatch::where('product_variant_id', $variant->id)
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
            notes: 'Penjualan POS',
            userId: $userId,
        );
    }
}
