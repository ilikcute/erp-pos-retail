<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'session_no'       => $this->session_no,
            'session_date'     => $this->session_date?->format('Y-m-d'),
            'status'           => $this->status,
            'opening_cash'     => (float) $this->opening_cash,
            'closing_cash'     => $this->closing_cash ? (float) $this->closing_cash : null,
            'expected_cash'    => $this->expected_cash ? (float) $this->expected_cash : null,
            'cash_difference'  => $this->cash_difference ? (float) $this->cash_difference : null,
            'total_sales'      => (float) $this->total_sales,
            'transaction_count' => $this->transaction_count,
            'notes'            => $this->notes,
            'closed_at'        => $this->closed_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'shift'            => new ShiftResource($this->whenLoaded('shift')),
            'cashier'          => new \App\Http\Resources\System\UserResource($this->whenLoaded('cashier')),
        ];
    }
}
