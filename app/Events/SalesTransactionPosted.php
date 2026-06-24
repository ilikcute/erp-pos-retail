<?php

namespace App\Events;

use App\Models\POS\SalesTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesTransactionPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SalesTransaction $transaction
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('pos'),
            new PrivateChannel('cashier.' . $this->transaction->cashier_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.posted';
    }

    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'transaction_no' => $this->transaction->transaction_no,
            'grand_total' => $this->transaction->grand_total,
            'cashier_id' => $this->transaction->cashier_id,
            'timestamp' => $this->transaction->posted_at,
        ];
    }
}
