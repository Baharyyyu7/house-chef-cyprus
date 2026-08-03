<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AdminDashboardService;
use App\Services\QueueHealthService;
use App\Services\SchedulerHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(AdminDashboardService $dashboard): View
    {
        return view('admin.dashboard', $dashboard->dashboard((int) request('period', 7)));
    }

    public function refreshQueue(QueueHealthService $queue): RedirectResponse
    {
        $queue->snapshot();

        return $this->refreshed('Queue sağlığı yenilendi.', 'admin_dashboard.queue_snapshot_refreshed');
    }

    public function refreshScheduler(SchedulerHealthService $scheduler): RedirectResponse
    {
        $scheduler->snapshot();

        return $this->refreshed('Scheduler sağlığı yenilendi.', 'admin_dashboard.scheduler_snapshot_refreshed');
    }

    public function refreshCache(): RedirectResponse
    {
        Cache::get('cache.health.probe');

        return $this->refreshed('Cache sağlığı yenilendi.', 'admin_dashboard.cache_probe_refreshed');
    }

    private function refreshed(string $message, string $action): RedirectResponse
    {
        Cache::forever('admin.system_health.last_refresh_at', now()->toIso8601String());
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => 'admin_dashboard',
            'auditable_id' => 0,
            'metadata' => ['source' => 'admin_operations_dashboard'],
        ]);

        return back()->with('status', $message);
    }
}
