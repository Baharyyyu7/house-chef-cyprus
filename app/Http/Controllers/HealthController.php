<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\HealthService;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(HealthService $health): JsonResponse
    {
        $snapshot = $health->snapshot();

        return response()->json(['status' => $snapshot['status'], 'checked_at' => $snapshot['checked_at']]);
    }

    public function detailed(HealthService $health): JsonResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return response()->json(['data' => $health->snapshot(true)]);
    }
}
