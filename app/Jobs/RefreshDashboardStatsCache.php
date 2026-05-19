<?php

namespace App\Jobs;

use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshDashboardStatsCache implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(config('blog.dashboard.refresh_queue', 'default'));
    }

    public function handle(DashboardStatsService $stats): void
    {
        $stats->refresh();
    }
}
