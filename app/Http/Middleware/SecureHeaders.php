<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecureHeaders
{
    /** @param Closure(Request):Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // Livewire and Alpine compile their declarative expressions at runtime. Blocking
        // unsafe-eval here prevents authentication, profile, and cooking interactions.
        $googleTagEnabled = ((bool) config('integrations.google.tag_manager.enabled') && preg_match('/^GTM-[A-Z0-9]+$/', (string) config('integrations.google.tag_manager.container_id')) === 1)
            || ((bool) config('integrations.google.analytics.enabled') && preg_match('/^G-[A-Z0-9]+$/', (string) config('integrations.google.analytics.measurement_id')) === 1);
        $googleScript = $googleTagEnabled ? ' https://www.googletagmanager.com' : '';
        $googleConnect = $googleTagEnabled ? ' https://www.google-analytics.com https://region1.google-analytics.com' : '';
        $googleFrame = $googleTagEnabled ? ' https://www.googletagmanager.com' : '';
        $adsenseEnabled = (bool) config('integrations.google.adsense.enabled')
            && preg_match('/^ca-pub-\\d+$/', (string) config('integrations.google.adsense.client_id')) === 1;
        $adsenseScript = $adsenseEnabled ? ' https://pagead2.googlesyndication.com' : '';
        $adsenseConnect = $adsenseEnabled ? ' https://pagead2.googlesyndication.com https://googleads.g.doubleclick.net' : '';
        $adsenseFrame = $adsenseEnabled ? ' https://googleads.g.doubleclick.net' : '';
        $scriptPolicy = "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$googleScript}{$adsenseScript}";
        $response->headers->set('Content-Security-Policy', "default-src 'self'; {$scriptPolicy}; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; img-src 'self' data: https:; font-src 'self' data: https://fonts.bunny.net; connect-src 'self'{$googleConnect}{$adsenseConnect}; frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com{$googleFrame}{$adsenseFrame}; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
