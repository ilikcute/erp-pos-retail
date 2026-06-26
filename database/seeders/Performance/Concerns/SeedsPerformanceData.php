<?php

namespace Database\Seeders\Performance\Concerns;

use Illuminate\Support\Facades\DB;

trait SeedsPerformanceData
{
    protected function performanceCount(): int
    {
        return max(500, (int) env('PERF_SEED_COUNT', 500));
    }

    protected function chunkSize(): int
    {
        return min(500, max(50, (int) env('PERF_SEED_CHUNK', 100)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function insertInChunks(string $table, array $rows): void
    {
        foreach (array_chunk($rows, $this->chunkSize()) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
