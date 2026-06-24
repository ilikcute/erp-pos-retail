<?php

namespace App\Actions\Integration;

use App\Models\POS\SalesPayment;
use Illuminate\Support\Facades\Log;

class ProcessPaymentGatewayAction
{
    public function __construct(
        private readonly string $gatewayType = 'stripe'
    ) {}

    public function processStripePayment(SalesPayment $payment, array $paymentMethodData): array
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $charge = $stripe->charges->create([
                'amount' => (int) ($payment->amount * 100),
                'currency' => 'usd',
                'source' => $paymentMethodData['token'],
                'description' => "Sales Transaction {$payment->sales_transaction_id}",
                'metadata' => [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->sales_transaction_id,
                ],
            ]);

            $payment->update([
                'gateway_transaction_id' => $charge->id,
                'status' => 'POSTED',
                'reference_no' => $charge->id,
            ]);

            Log::info("Stripe payment processed: {$charge->id}");

            return [
                'success' => true,
                'transaction_id' => $charge->id,
                'amount' => $payment->amount,
            ];
        } catch (\Exception $e) {
            Log::error("Stripe payment failed: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundStripePayment(SalesPayment $payment): array
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $refund = $stripe->refunds->create([
                'charge' => $payment->gateway_transaction_id,
                'reason' => 'requested_by_customer',
            ]);

            $payment->update(['status' => 'REFUNDED']);

            Log::info("Stripe refund processed: {$refund->id}");

            return [
                'success' => true,
                'refund_id' => $refund->id,
            ];
        } catch (\Exception $e) {
            Log::error("Stripe refund failed: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
