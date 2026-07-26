<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', ['recipeCount' => Recipe::count(), 'userCount' => User::count(), 'sourceCount' => DataSource::count()]);
    }
}
