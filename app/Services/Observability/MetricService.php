<?php

declare(strict_types=1);

namespace App\Services\Observability;

use App\Models\SystemMetric;
use Illuminate\Http\Request;

final class MetricService
{
    public function recordRequest(Request $request, int $status, int $duration): void
    {
        $route = optional($request->route())->getName() ?: $request->path();
        foreach (['requests' => 1, 'duration_ms' => $duration, 'errors' => $status >= 500 ? 1 : 0] as $metric => $value) {
            SystemMetric::query()->updateOrCreate(['metric' => $metric, 'dimension' => $route], ['value' => $value, 'recorded_at' => now()]);
        }
    }

    /** @return array<string, float|int> */
    public function summary(int $days = 1): array
    {
        $since = now()->subDays(in_array($days, [1, 7, 30], true) ? $days : 1);
        $rows = SystemMetric::query()->where('recorded_at', '>=', $since)->get()->groupBy('metric');
        $requests = (float) ($rows->get('requests')?->sum('value') ?? 0);
        $duration = (float) ($rows->get('duration_ms')?->sum('value') ?? 0);

        return ['requests' => (int) $requests, 'errors' => (int) ($rows->get('errors')?->sum('value') ?? 0), 'average_duration_ms' => $requests > 0 ? round($duration / $requests, 2) : 0, 'period_days' => $days];
    }
}
