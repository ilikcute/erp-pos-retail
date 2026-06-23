<?php

namespace App\Services\System;

use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
    public function generate(string $table, string $column, string $prefix, int $padding = 5): string
    {
        return DB::transaction(function () use ($table, $column, $prefix, $padding) {
            // Ambil record terakhir dengan lockForUpdate
            $lastRecord = DB::table($table)
                ->where($column, 'like', $prefix . '%')
                ->orderByDesc($column) // Urutkan desc karena format U00001, U00002
                ->lockForUpdate()
                ->first();

            $lastNumber = 0;
            if ($lastRecord) {
                // Ambil bagian angka dari code terakhir
                $lastNumber = (int) substr($lastRecord->{$column}, strlen($prefix));
            }

            $newNumber = $lastNumber + 1;
            return $prefix . str_pad($newNumber, $padding, '0', STR_PAD_LEFT);
        });
    }
}
