<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use Illuminate\Http\RedirectResponse;

class AffiliateController extends Controller
{
    public function redirect(string $slug): RedirectResponse
    {
        $link = AffiliateLink::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $host = mb_strtolower((string) parse_url($link->target_url, PHP_URL_HOST));
        $scheme = mb_strtolower((string) parse_url($link->target_url, PHP_URL_SCHEME));
        $allowedHosts = array_map('mb_strtolower', config('monetization.affiliate_allowed_hosts', []));

        abort_unless($scheme === 'https' && $host !== '' && in_array($host, $allowedHosts, true), 404);

        $link->recordClick();

        return redirect()->away($link->target_url);
    }
}
