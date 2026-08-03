<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MailOperationsService;
use Illuminate\View\View;

final class MailOperationsController extends Controller
{
    public function __invoke(MailOperationsService $service): View
    {
        return view('admin.mail-operations.index', ['mail' => $service->snapshot()]);
    }
}
