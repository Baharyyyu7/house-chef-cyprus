<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // www.housechefcyprus.com -> housechefcyprus.com 301 redirect
        if (str_starts_with(strtolower($host), 'www.')) {
            $canonicalHost = substr($host, 4);
            $canonicalUrl = 'https://'.$canonicalHost.$request->getRequestUri();

            return redirect()->away($canonicalUrl, 301);
        }

        return $next($request);
    }
}
