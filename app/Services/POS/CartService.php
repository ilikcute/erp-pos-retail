<?php

namespace App\Services\POS;

use App\Models\POS\Cart;
use App\Models\POS\HeldCart;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionPayment;
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
                'label' => $label ?? 'Transaksi ' . now()->format('H:i'),
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

    public function checkout(int $userId, array $data): Transaction
    {
        return DB::transaction(function () use ($userId, $data) {
            $carts = Cart::where('user_id', $userId)->with('product')->get();

            $transaction = Transaction::create([
                'user_id' => $userId,
                'customer_id' => $data['customer_id'],
                'subtotal' => $data['grand_total'],
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax_total'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'grand_total' => $data['grand_total'],
                'cash' => $data['cash'] ?? 0,
                'change' => $data['change'] ?? 0,
                'payment_gateway' => $data['payment_gateway'] ?? null,
                'pay_later' => $data['pay_later'] ?? false,
                'due_date' => $data['due_date'] ?? null,
                'status' => $data['pay_later'] ? 'unpaid' : 'paid',
            ]);

            // Create transaction items
            foreach ($carts as $cart) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $cart->product_id,
                    'qty' => $cart->qty,
                    'price' => $cart->sell_price,
                    'subtotal' => $cart->sell_price * $cart->qty,
                ]);

                // Reduce stock
                $cart->product->decrement('stock', $cart->qty);
            }

            // Handle payment splits
            if (!empty($data['payment_splits'])) {
                foreach ($data['payment_splits'] as $split) {
                    TransactionPayment::create([
                        'transaction_id' => $transaction->id,
                        'method' => $split['method'],
                        'amount' => $split['amount'],
                        'reference' => $split['reference'] ?? null,
                    ]);
                }
            }

            // Clear cart
            $this->clearCart($userId);

            return $transaction;
        });
    }
}
