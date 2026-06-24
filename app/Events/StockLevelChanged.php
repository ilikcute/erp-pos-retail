<?php

namespace App\Events;

use App\Models\Inventory\InventoryLedger;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLevelChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public InventoryLedger $ledger,
        public float $previousBalance,
        public float $currentBalance,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('inventory'),
            new Channel('location.' . $this->ledger->location_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.changed';
    }

    public function broadcastWith(): array
    {
        $isLow = $this->currentBalance <= 10;

        return [
            'product_id' => $this->ledger->product_id,
            'location_id' => $this->ledger->location_id,
            'previous_balance' => $this->previousBalance,
            'current_balance' => $this->currentBalance,
            'movement_type' => $this->ledger->movement_type,
            'is_low_stock' => $isLow,
            'timestamp' => now(),
        ];
    }
}
