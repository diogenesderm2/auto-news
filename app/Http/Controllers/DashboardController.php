<?php

namespace App\Http\Controllers;

use App\Jobs\RefreshDashboardStatsCache;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStatsService $stats): Response
    {
        $dashboard = $stats->get();

        if ($dashboard === null) {
            RefreshDashboardStatsCache::dispatchSync();
            $dashboard = $stats->get() ?? $stats->refresh();
            $source = 'live';
        } else {
            RefreshDashboardStatsCache::dispatch();
            $source = 'cache';
        }

        $dashboard['meta']['source'] = $source;

        return Inertia::render('Dashboard', [
            'dashboard' => $dashboard,
        ]);
    }
}
