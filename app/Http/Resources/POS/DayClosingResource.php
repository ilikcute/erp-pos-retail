<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DayClosingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'closing_date' => $this->closing_date->format('Y-m-d'),
            'closing_number' => $this->closing_number,
            'location_id' => $this->location_id,
            'location_name' => $this->location?->name,

            // Ringkasan transaksi
            'total_transactions' => (int) $this->total_transactions,
            'total_sales' => (float) $this->total_sales,
            'total_cash' => (float) $this->total_cash,
            'total_non_cash' => (float) $this->total_non_cash,
            'total_discount' => (float) $this->total_discount,
            'total_tax' => (float) $this->total_tax,

            // Cash reconciliation
            'total_opening_cash' => (float) $this->total_opening_cash,
            'total_closing_cash' => (float) $this->total_closing_cash,
            'total_expected_cash' => (float) $this->total_expected_cash,
            'cash_difference' => (float) $this->cash_difference,

            // Status
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'closed_by_name' => $this->closedByUser?->name,
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),

            // Sessions detail
            'sessions' => $this->whenLoaded('sessions', function () {
                return $this->sessions->map(fn($s) => [
                    'id' => $s->id,
                    'session_no' => $s->session_no,
                    'user_name' => $s->user?->name,
                    'shift_name' => $s->shift?->name ?? $s->shift?->shift_name,
                    'opening_cash' => (float) $s->opening_cash,
                    'closing_cash' => (float) $s->closing_cash,
                    'expected_cash' => (float) $s->expected_cash,
                    'cash_difference' => (float) $s->cash_difference,
                    'total_sales' => (float) $s->total_sales,
                    'total_transactions' => (int) $s->total_transactions,
                    'opened_at' => $s->opened_at?->format('H:i'),
                    'closed_at' => $s->closed_at?->format('H:i'),
                ]);
            }),
        ];
    }
}
