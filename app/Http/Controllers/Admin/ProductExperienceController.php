<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductExperienceAuditService;
use Illuminate\View\View;

final class ProductExperienceController extends Controller
{
    public function __invoke(ProductExperienceAuditService $audit): View
    {
        return view('admin.product-experience.index', ['report' => $audit->latest()]);
    }
}
