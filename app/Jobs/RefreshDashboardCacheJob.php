<?php

namespace App\Jobs;

use App\Actions\Reporting\GenerateDashboardKPIsAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshDashboardCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public function handle(GenerateDashboardKPIsAction $action): void
    {
        try {
            $kpis = $action->execute();
            Cache::put('dashboard_kpis_latest', $kpis, now()->addHours(1));
            Log::info('Dashboard cache refreshed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to refresh dashboard cache: '.$e->getMessage());
            throw $e;
        }
    }
}
