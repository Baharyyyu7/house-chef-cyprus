<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\HealthService;
use App\Services\ProductionOperationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HealthController extends Controller
{
    public function __invoke(Request $request, HealthService $health, ProductionOperationsService $operations): JsonResponse
    {
        $snapshot = $health->snapshot();

        // Bileşen ayrıntısı (hangi görev bayat) yalnız yöneticiye açık;
        // herkese açık yanıt durum ve sürüm commit'iyle sınırlı.
        $payload = [
            'status' => $snapshot['status'],
            'checked_at' => $snapshot['checked_at'],
            'commit' => $operations->buildCommit(),
        ];

        return response()->json($request->is('api/*') ? ['data' => $payload] : $payload);
    }

    public function detailed(HealthService $health): JsonResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return response()->json(['data' => $health->snapshot(true)]);
    }
}
