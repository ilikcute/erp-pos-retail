<?php

namespace App\Listeners;

use App\Events\StockLevelChanged;
use App\Models\System\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyLowStockAlert implements ShouldQueue
{
    public function handle(StockLevelChanged $event): void
    {
        if ($event->isLow) {
            $event->ledger->loadMissing('variant');
            $productId = $event->ledger->variant?->product_id;

            Notification::create([
                'user_id' => null,
                'type' => 'LOW_STOCK_ALERT',
                'title' => 'Low Stock Alert',
                'message' => "Product ID: {$productId} is running low in location {$event->ledger->location_id}",
                'data' => [
                    'product_id' => $productId,
                    'current_balance' => $event->currentBalance,
                ],
                'read_at' => null,
            ]);
        }
    }
}
