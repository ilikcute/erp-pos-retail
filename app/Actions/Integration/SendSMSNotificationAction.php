<?php

namespace App\Actions\Integration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSMSNotificationAction
{
    public function sendTransactionReceipt(string $phoneNumber, array $transactionData): array
    {
        try {
            $message = $this->formatTransactionMessage($transactionData);

            $response = Http::post(config('services.twilio.endpoint'), [
                'To' => $phoneNumber,
                'From' => config('services.twilio.phone'),
                'Body' => $message,
            ])->withBasicAuth(
                config('services.twilio.account_sid'),
                config('services.twilio.auth_token')
            );

            Log::info("SMS sent to {$phoneNumber}");

            return [
                'success' => true,
                'message_sid' => $response->json('sid'),
            ];
        } catch (\Exception $e) {
            Log::error("SMS send failed: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendLowStockAlert(string $phoneNumber, array $productData): array
    {
        try {
            $message = "⚠️ LOW STOCK ALERT\n{$productData['product_name']}\nCurrent: {$productData['current_qty']} units\nReorder Level: {$productData['reorder_level']}";

            $response = Http::post(config('services.twilio.endpoint'), [
                'To' => $phoneNumber,
                'From' => config('services.twilio.phone'),
                'Body' => $message,
            ])->withBasicAuth(
                config('services.twilio.account_sid'),
                config('services.twilio.auth_token')
            );

            return [
                'success' => true,
                'message_sid' => $response->json('sid'),
            ];
        } catch (\Exception $e) {
            Log::error("Low stock SMS failed: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function formatTransactionMessage(array $data): string
    {
        return "✅ RECEIPT\n" .
               "Transaction: {$data['transaction_no']}\n" .
               "Amount: {$data['grand_total']}\n" .
               "Date: {$data['transaction_date']}\n" .
               "Thank you for your purchase!";
    }
}
