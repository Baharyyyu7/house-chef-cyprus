<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\TipContribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function redirect(string $slug): RedirectResponse
    {
        $link = AffiliateLink::where('slug', $slug)->firstOrFail();
        $link->recordClick();

        return redirect()->away($link->target_url);
    }
}
