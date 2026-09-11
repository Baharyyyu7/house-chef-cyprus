<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dil önekli adresleri karşılar: /en/tarifler → /tarifler, dil "en".
 *
 * Yönlendirme öncesi çalışması gerekiyor, o yüzden global middleware. Rotalar
 * ikinci kez tanımlanmıyor; istek yolundan önek çıkarılıp normal rotaya
 * düşürülüyor. Böylece bütün rota adları tek kalıyor ve mevcut bağlantılar
 * olduğu gibi çalışıyor.
 *
 * Neden gerekli: dil çerezle seçildiği sürece bütün diller aynı adresi
 * paylaşıyordu ve Googlebot çerez taşımadığı için Türkçe dışındaki hiçbir
 * sürümü hiç görmedi. hreflang de yazılamıyordu; hreflang farklı URL'leri
 * bağlar ve bağlayacak URL yoktu.
 *
 * Kapalı bir dilin öneki 404 verir. Kapalı dile sayfa açmak, Türkçe içeriği
 * yabancı dil sayfası gibi sunmak olurdu.
 */
final class LocaleUrlPrefix
{
    public function handle(Request $request, Closure $next): Response
    {
        $ilkParca = $request->segment(1);

        if (! is_string($ilkParca) || $ilkParca === '') {
            return $next($request);
        }

        $diller = (array) config('locales.supported', []);

        if (! array_key_exists($ilkParca, $diller)) {
            return $next($request);
        }

        // Bağımsız makine keşif, AI ve besleme uçları dil öneki soyulmasına girmez.
        $tamYol = trim((string) $request->path(), '/');
        if (in_array($tamYol, [
            'en/llms.txt', 'en/llms-full.txt', 'en/feed', 'en/feed.xml', 'en/rss.xml',
            'en/opensearch.xml', 'en/manifest.webmanifest', 'en/site.webmanifest'
        ], true)) {
            return $next($request);
        }

        // Varsayılan dil kökte yaşar. /tr/... ayrı bir adres olarak durursa
        // aynı içerik iki adreste görünür; kökteki sürüme kalıcı yönlendirilir.
        if ($ilkParca === (string) config('locales.default', 'tr')) {
            return redirect($this->onekSizYol($request), 301);
        }

        if (($diller[$ilkParca]['enabled'] ?? false) !== true) {
            abort(404);
        }

        app()->setLocale($ilkParca);
        $request->setLocale($ilkParca);
        // SetLocale sonradan çalışıyor ve çereze bakıyor; seçimin URL'den
        // geldiğini oradan da görebilmesi için işaretlenir.
        $request->attributes->set('hc_url_locale', $ilkParca);

        return $next($this->onekiSoyulmusIstek($request));
    }

    private function onekSizYol(Request $request): string
    {
        $yol = '/'.ltrim((string) $request->path(), '/');
        $parcali = substr($yol, strlen('/'.$request->segment(1))) ?: '/';

        return $parcali.($request->getQueryString() !== null ? '?'.$request->getQueryString() : '');
    }

    private function onekiSoyulmusIstek(Request $request): Request
    {
        $yol = '/'.ltrim((string) $request->path(), '/');
        $yeni = substr($yol, strlen('/'.$request->segment(1))) ?: '/';

        $sorgu = $request->getQueryString();
        $request->server->set('REQUEST_URI', $yeni.($sorgu !== null ? '?'.$sorgu : ''));

        // Symfony yolu önbelleğe alıyor; yeniden çözülmesi için tazelenir.
        return Request::createFromBase($request->duplicate());
    }
}
