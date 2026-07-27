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
}
