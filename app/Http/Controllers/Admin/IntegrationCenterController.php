<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationReadinessService;
use Illuminate\View\View;

final class IntegrationCenterController extends Controller
{
    public function __invoke(IntegrationReadinessService $service): View
    {
        return view('admin.integrations.index', ['integrations' => $service->snapshot()]);
    }
}
