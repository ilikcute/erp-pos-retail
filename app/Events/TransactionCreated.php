<?php

namespace App\Events;

use App\Models\POS\SalesTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public SalesTransaction $transaction
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('pos.session.' . $this->transaction->cashier_session_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'event' => 'TransactionCreated',
            'data' => [
                'transaction_no' => $this->transaction->transaction_no,
                'grand_total' => $this->transaction->grand_total,
                'payment_status' => $this->transaction->payment_status,
                'cashier' => $this->transaction->cashier->name,
                'session_id' => $this->transaction->cashier_session_id,
                'created_at' => $this->transaction->created_at->toIso8601String(),
            ],
        ];
    }
}
