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

        // Bağımsız makine keşif, AI ve besleme uçları dil öneki soyulmasına
        // girmez: bunlar rotalarını dil önekiyle birlikte kendileri tanımlar.
        //
        // Bu liste eskiden 'en/llms.txt' gibi sabit satırlardı ve her yeni dil
        // için elle büyütülmesi gerekiyordu. Dil eklemenin bir yerde elle
        // satır yazmayı gerektirmesi, o dilin sessizce eksik kalmasının en
        // olası yolu - o yüzden dosya adı listeleniyor, dil değil.
        $tamYol = trim((string) $request->path(), '/');
        $makineUclari = [
            'llms.txt', 'llms-full.txt', 'feed', 'feed.xml', 'rss.xml',
            'opensearch.xml', 'manifest.webmanifest', 'site.webmanifest',
        ];

        foreach ($makineUclari as $uc) {
            if ($tamYol === $ilkParca.'/'.$uc) {
                return $next($request);
            }
        }

        // Varsayılan dil kökte yaşar. /tr/... ayrı bir adres olarak durursa
        // aynı içerik iki adreste görünür; kökteki sürüme kalıcı yönlendirilir.
        if ($ilkParca === (string) config('locales.default', 'tr')) {
            return redirect($this->onekSizYol($request), 301);
        }

        if (($diller[$ilkParca]['enabled'] ?? false) !== true) {
            abort(404);
        }

        // Yol parçası dile çevrilir: /en/tarifler/... yerine /en/recipes/...
        // İngilizce arayan ve İngilizce tarayan yolu da okur. Türkçe yazımla
        // gelen istek kalıcı olarak yerelleşmiş adrese gider; aynı içerik iki
        // adreste durmaz.
        $takma = (array) (config('locales.path_aliases')[$ilkParca] ?? []);
        $ikinciParca = (string) $request->segment(2);

        if ($ikinciParca !== '' && isset($takma[$ikinciParca])) {
            return redirect($this->yolDegistir($request, $takma[$ikinciParca]), 301);
        }

        $tersTakma = array_flip($takma);

        app()->setLocale($ilkParca);
        $request->setLocale($ilkParca);
        // SetLocale sonradan çalışıyor ve çereze bakıyor; seçimin URL'den
        // geldiğini oradan da görebilmesi için işaretlenir.
        $request->attributes->set('hc_url_locale', $ilkParca);

        return $next($this->onekiSoyulmusIstek($request, $tersTakma[$ikinciParca] ?? null));
    }

    private function onekSizYol(Request $request): string
    {
        $yol = '/'.ltrim((string) $request->path(), '/');
        $parcali = substr($yol, strlen('/'.$request->segment(1))) ?: '/';

        return $parcali.($request->getQueryString() !== null ? '?'.$request->getQueryString() : '');
    }

    /** Dil önekli yolun ikinci parçasını değiştirip tam adresi döner. */
    private function yolDegistir(Request $request, string $yeniParca): string
    {
        $parcalar = explode('/', trim((string) $request->path(), '/'));
        $parcalar[1] = $yeniParca;
        $sorgu = $request->getQueryString();

        return '/'.implode('/', $parcalar).($sorgu !== null ? '?'.$sorgu : '');
    }

    private function onekiSoyulmusIstek(Request $request, ?string $rotaParcasi = null): Request
    {
        $yol = '/'.ltrim((string) $request->path(), '/');
        $yeni = substr($yol, strlen('/'.$request->segment(1))) ?: '/';

        // Yerelleşmiş parça iç rota adına geri çevrilir: /recipes → /tarifler.
        if ($rotaParcasi !== null) {
            $parcalar = explode('/', trim($yeni, '/'));
            $parcalar[0] = $rotaParcasi;
            $yeni = '/'.implode('/', $parcalar);
        }

        $sorgu = $request->getQueryString();
        $request->server->set('REQUEST_URI', $yeni.($sorgu !== null ? '?'.$sorgu : ''));

        // Symfony yolu önbelleğe alıyor; yeniden çözülmesi için tazelenir.
        return Request::createFromBase($request->duplicate());
    }
}
