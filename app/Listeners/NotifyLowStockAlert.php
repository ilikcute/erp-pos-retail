<?php

namespace App\Listeners;

use App\Events\StockLevelChanged;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyLowStockAlert implements ShouldQueue
{
    public function handle(StockLevelChanged $event): void
    {
        if ($event->isLow) {
            Notification::create([
                'user_id'       => null,
                'type'          => 'LOW_STOCK_ALERT',
                'title'         => 'Low Stock Alert',
                'message'       => "Product {$event->ledger->product_id} is running low in location {$event->ledger->location_id}",
                'data'          => [
                    'product_id' => $event->ledger->product_id,
                    'current_balance' => $event->currentBalance,
                ],
                'read_at'       => null,
            ]);
        }
    }
}
