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
        // Google'ın kendi CMP'si (Privacy & messaging) ayrı alan adlarından
        // gelir. CSP bunları tanımazsa onay mesajı sessizce engellenir; mesaj
        // görünmeyince onay oluşmaz ve hiç reklam çıkmaz. Bu alan adları
        // yalnız o CMP seçiliyken açılır.
        $googleCmp = $adsenseEnabled && config('integrations.google.adsense.cmp_provider') === 'google';
        $cmpScript = $googleCmp ? ' https://fundingchoicesmessages.google.com' : '';
        $cmpConnect = $googleCmp ? ' https://fundingchoicesmessages.google.com' : '';
        $cmpFrame = $googleCmp ? ' https://fundingchoicesmessages.google.com' : '';

        $adsenseScript = $adsenseEnabled ? ' https://pagead2.googlesyndication.com https://tpc.googlesyndication.com'.$cmpScript : '';
        $adsenseConnect = $adsenseEnabled ? ' https://pagead2.googlesyndication.com https://googleads.g.doubleclick.net'.$cmpConnect : '';
        $adsenseFrame = $adsenseEnabled ? ' https://googleads.g.doubleclick.net https://tpc.googlesyndication.com'.$cmpFrame : '';
        $scriptPolicy = "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$googleScript}{$adsenseScript}";
        if ($request->is('embed/*')) {
            $response->headers->remove('X-Frame-Options');
            $response->headers->set('Content-Security-Policy', "default-src 'self'; {$scriptPolicy}; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; img-src 'self' data: https:; font-src 'self' data: https://fonts.bunny.net; connect-src 'self'{$googleConnect}{$adsenseConnect}; frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com{$googleFrame}{$adsenseFrame}; frame-ancestors *; base-uri 'self'; form-action 'self'");
            $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
        } else {
            $response->headers->set('Content-Security-Policy', "default-src 'self'; {$scriptPolicy}; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; img-src 'self' data: https:; font-src 'self' data: https://fonts.bunny.net; connect-src 'self'{$googleConnect}{$adsenseConnect}; frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com{$googleFrame}{$adsenseFrame}; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
