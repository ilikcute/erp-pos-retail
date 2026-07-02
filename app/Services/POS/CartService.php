<?php

namespace App\Services\POS;

use App\Models\Accounting\PaymentMethod;
use App\Models\POS\Cart;
use App\Models\POS\HeldCart;
use App\Models\POS\SalesPayment;
use App\Models\POS\SalesSession;
use App\Models\POS\SalesTransaction;
use App\Models\POS\SalesTransactionItem;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function addToCart(int $userId, int $productId, int $qty, float $price): Cart
    {
        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cart) {
            $cart->increment('qty', $qty);
        } else {
            $cart = Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'qty' => $qty,
                'sell_price' => $price,
            ]);
        }

        return $cart;
    }

    public function updateQty(int $userId, int $cartId, int $qty): void
    {
        Cart::where('id', $cartId)
            ->where('user_id', $userId)
            ->update(['qty' => $qty]);
    }

    public function removeFromCart(int $userId, int $cartId): void
    {
        Cart::where('id', $cartId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function clearCart(int $userId): void
    {
        Cart::where('user_id', $userId)->delete();
    }

    public function holdCart(int $userId, ?string $label = null): HeldCart
    {
        return DB::transaction(function () use ($userId, $label) {
            $carts = Cart::where('user_id', $userId)->with('product')->get();

            $heldCart = HeldCart::create([
                'user_id' => $userId,
                'label' => $label ?? 'Transaksi '.now()->format('H:i'),
            ]);

            foreach ($carts as $cart) {
                $heldCart->items()->create([
                    'product_id' => $cart->product_id,
                    'qty' => $cart->qty,
                    'price' => $cart->sell_price,
                ]);
            }

            $this->clearCart($userId);

            return $heldCart;
        });
    }

    public function recallHeldCart(int $userId, int $heldCartId): void
    {
        DB::transaction(function () use ($userId, $heldCartId) {
            $heldCart = HeldCart::where('id', $heldCartId)
                ->where('user_id', $userId)
                ->with('items')
                ->firstOrFail();

            foreach ($heldCart->items as $item) {
                $this->addToCart($userId, $item->product_id, $item->qty, $item->price);
            }

            $heldCart->items()->delete();
            $heldCart->delete();
        });
    }

    public function checkout(int $userId, array $data): SalesTransaction
    {
        return DB::transaction(function () use ($userId, $data) {
            $carts = Cart::where('user_id', $userId)->with('product.variants')->get();

            // Find active SalesSession for the cashier
            $salesSession = SalesSession::where('cashier_id', $userId)
                ->where('status', 'OPEN')
                ->first();

            if (! $salesSession) {
                $salesSession = SalesSession::where('status', 'OPEN')->first();
            }

            if (! $salesSession) {
                throw new \RuntimeException('Sesi penjualan (Sales Session) aktif tidak ditemukan.');
            }

            // Generate transaction number
            $documentNumberService = app(DocumentNumberService::class);
            $transactionNo = $documentNumberService->generate('SALES_TRANSACTION');

            $transaction = SalesTransaction::create([
                'transaction_no' => $transactionNo,
                'sales_session_id' => $salesSession->id,
                'cashier_session_id' => $data['cashier_session_id'] ?? null,
                'cashier_id' => $userId,
                'customer_id' => $data['customer_id'],
                'transaction_date' => now()->toDateString(),
                'status' => 'POSTED',
                'subtotal' => $data['subtotal'] ?? $data['grand_total'],
                'discount_amount' => $data['discount'] ?? 0,
                'tax_amount' => $data['tax_total'] ?? 0,
                'grand_total' => $data['grand_total'],
                'paid_amount' => $data['cash'] ?? 0,
                'change_amount' => $data['change'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Create transaction items
            foreach ($carts as $cart) {
                $variant = $cart->product->variants->first();

                // Find pricing info from payload
                $itemPricing = collect($data['items_pricing'] ?? [])->firstWhere('cart_id', $cart->id);
                $discountAmount = $itemPricing['discount_amount'] ?? 0;
                $taxAmount = $itemPricing['tax_amount'] ?? 0;
                $lineTotal = $itemPricing['line_total'] ?? ($cart->sell_price * $cart->qty);

                SalesTransactionItem::create([
                    'sales_transaction_id' => $transaction->id,
                    'product_id' => $cart->product_id,
                    'product_variant_id' => $variant?->id,
                    'item_name' => $cart->product->product_name,
                    'sku' => $variant?->sku ?? $cart->product->product_code,
                    'barcode' => $variant?->barcodes()->first()?->barcode ?? $cart->product->barcode,
                    'unit_id' => $cart->product->base_unit_id,
                    'quantity' => $cart->qty,
                    'unit_price' => $cart->sell_price,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                    'cost_price' => $variant?->purchase_price ?? 0,
                    'created_by' => $userId,
                ]);
            }

            // Handle payment splits
            if (! empty($data['payment_splits'])) {
                foreach ($data['payment_splits'] as $split) {
                    $paymentNo = $documentNumberService->generate('SALES_PAYMENT');

                    // Resolve payment method id
                    $pmId = $split['payment_method_id'] ?? null;
                    if (! $pmId && ! empty($split['method'])) {
                        $pmId = PaymentMethod::where('method_type', strtoupper($split['method']))
                            ->where('is_active', true)
                            ->value('id');
                    }
                    if (! $pmId) {
                        $pmId = PaymentMethod::where('method_type', 'CASH')
                            ->where('is_active', true)
                            ->value('id');
                    }

                    SalesPayment::create([
                        'payment_no' => $paymentNo,
                        'sales_transaction_id' => $transaction->id,
                        'payment_method_id' => $pmId,
                        'amount' => $split['amount'],
                        'reference_no' => $split['reference'] ?? null,
                        'status' => 'POSTED',
                        'created_by' => $userId,
                    ]);
                }
            } else {
                $paymentNo = $documentNumberService->generate('SALES_PAYMENT');

                // Resolve payment method id
                $pmId = $data['payment_method_id'] ?? null;
                if (! $pmId && ! empty($data['payment_gateway'])) {
                    $pmId = PaymentMethod::where('method_type', strtoupper($data['payment_gateway']))
                        ->where('is_active', true)
                        ->value('id');
                }
                if (! $pmId) {
                    $pmId = PaymentMethod::where('method_type', 'CASH')
                        ->where('is_active', true)
                        ->value('id');
                }

                SalesPayment::create([
                    'payment_no' => $paymentNo,
                    'sales_transaction_id' => $transaction->id,
                    'payment_method_id' => $pmId,
                    'amount' => $data['grand_total'],
                    'reference_no' => $data['payment_gateway'] ?? null,
                    'status' => 'POSTED',
                    'created_by' => $userId,
                ]);
            }

            // Clear cart
            $this->clearCart($userId);

            return $transaction;
        });
    }
}
