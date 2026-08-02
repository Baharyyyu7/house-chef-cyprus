<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Personalization\PersonalizationOperationsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PersonalizationOperationsController extends Controller
{
    public function index(Request $request, PersonalizationOperationsService $operations): View
    {
        return view('admin.personalization.index', ['report' => $operations->report()]);
    }
}
