<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashierSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_no' => $this->session_no,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'shift_id' => $this->shift_id,
            'shift_name' => $this->shift?->name ?? $this->shift?->shift_name,
            'location_id' => $this->location_id,
            'location_name' => $this->location?->name,
            'opening_cash' => (float) $this->opening_cash,
            'closing_cash' => $this->closing_cash !== null ? (float) $this->closing_cash : null,
            'expected_cash' => (float) $this->expected_cash,
            'cash_difference' => (float) $this->cash_difference,
            'total_sales' => (float) $this->total_sales,
            'total_transactions' => (int) $this->total_transactions,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'opened_at' => $this->opened_at?->format('Y-m-d H:i:s'),
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
            'closed_by_name' => $this->closedByUser?->name,
            'duration_minutes' => $this->closed_at
                ? (int) $this->opened_at->diffInMinutes($this->closed_at)
                : ($this->opened_at ? (int) $this->opened_at->diffInMinutes(now()) : 0),
        ];
    }
}
