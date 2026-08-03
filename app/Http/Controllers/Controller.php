<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    /**
     * Use the central Gate rather than duplicating role checks in controllers.
     */
    protected function authorizeAdminOperations(): void
    {
        Gate::authorize('access-admin');
    }
}
