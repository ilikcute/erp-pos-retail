<?php

namespace App\Services\System;

use App\Models\System\DocumentSequence;
use Illuminate\Support\Facades\DB;

class DocumentSequenceService
{
    public function generateNumber(string $documentCode): string
    {
        return DB::transaction(function () use ($documentCode) {
            $sequence = DocumentSequence::where('document_code', $documentCode)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                throw new \Exception("Document sequence '{$documentCode}' not found");
            }

            // Check if need to reset
            $this->checkAndReset($sequence);

            // Increment
            $sequence->increment('current_number');
            $sequence->refresh();

            // Format number
            $number = str_pad($sequence->current_number, 4, '0', STR_PAD_LEFT);

            return $sequence->prefix.'-'.now()->format('Ymd').'-'.$number;
        });
    }

    private function checkAndReset(DocumentSequence $sequence): void
    {
        $shouldReset = match ($sequence->reset_period) {
            'DAILY' => ! $sequence->last_reset_at || $sequence->last_reset_at->isToday() === false,
            'MONTHLY' => ! $sequence->last_reset_at || $sequence->last_reset_at->format('Y-m') !== now()->format('Y-m'),
            'YEARLY' => ! $sequence->last_reset_at || $sequence->last_reset_at->format('Y') !== now()->format('Y'),
            default => false,
        };

        if ($shouldReset) {
            $sequence->update([
                'current_number' => 0,
                'last_reset_at' => now(),
            ]);
        }
    }
}
