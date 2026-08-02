<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReleaseCandidateAuditService;
use Illuminate\View\View;

final class ReleaseCandidateController extends Controller
{
    public function __invoke(ReleaseCandidateAuditService $audit): View
    {
        return view('admin.release-candidate.index', ['report' => $audit->latest()]);
    }
}
