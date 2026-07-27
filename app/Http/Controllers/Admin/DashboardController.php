<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\Recipe;
use App\Models\User;
use App\Services\QueueHealthService;
use App\Services\SchedulerHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(QueueHealthService $queue, SchedulerHealthService $scheduler): View
    {
        return view('admin.dashboard', ['recipeCount' => Recipe::count(), 'userCount' => User::count(), 'sourceCount' => DataSource::count(), 'queueHealth' => $queue->snapshot(), 'schedulerHealth' => $scheduler->snapshot(), 'cacheHealth' => ['driver' => config('cache.default'), 'status' => 'healthy'], 'lastRefreshAt' => Cache::get('admin.system_health.last_refresh_at')]);
    }

    public function refreshQueue(QueueHealthService $queue): RedirectResponse
    {
        $queue->snapshot();

        return $this->refreshed('Queue sağlığı yenilendi.');
    }

    public function refreshScheduler(SchedulerHealthService $scheduler): RedirectResponse
    {
        $scheduler->snapshot();

        return $this->refreshed('Scheduler sağlığı yenilendi.');
    }

    public function refreshCache(): RedirectResponse
    {
        Cache::get('cache.health.probe');

        return $this->refreshed('Cache sağlığı yenilendi.');
    }

    private function refreshed(string $message): RedirectResponse
    {
        Cache::forever('admin.system_health.last_refresh_at', now()->toIso8601String());

        return back()->with('status', $message);
    }
}
