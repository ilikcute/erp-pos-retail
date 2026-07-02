<?php

namespace App\Support;

use App\Models\System\DocumentSequence;
use App\Models\System\DocumentType;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate nomor dokumen unik secara atomic.
     *
     * Format: {PREFIX}{SEP}{DATE}{SEP}{SEQUENCE}
     * Contoh: POS-20260621-0001
     *
     * @throws \RuntimeException jika document type tidak ditemukan
     */
    public function generate(string $typeCode): string
    {
        return DB::transaction(function () use ($typeCode) {
            $type = DocumentType::where('code', $typeCode)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $period = $type->date_format
                ? now()->format($type->date_format)
                : now()->format('Ymd');

            $sequence = DocumentSequence::where('document_type_id', $type->id)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if ($sequence) {
                $sequence->increment('last_sequence');
            } else {
                $sequence = DocumentSequence::create([
                    'document_type_id' => $type->id,
                    'period' => $period,
                    'last_sequence' => 1,
                ]);
            }

            $sep = $type->separator ?? '-';
            $padded = str_pad($sequence->last_sequence, $type->padding ?? 4, '0', STR_PAD_LEFT);

            $parts = array_filter([$type->prefix, $period, $padded]);

            return implode($sep, $parts);
        });
    }
}
