<?php

declare(strict_types=1);

namespace App\Services\Seo;

final class SeoProviderHealthService
{
    /** @return array<string, array{enabled:bool,configured:bool,ready:bool,safe_message:string}> */
    public function snapshot(): array
    {
        $searchConsole = (array) config('seo.search_console');
        $indexNow = (array) config('seo.indexnow');
        $social = (array) config('seo.social');

        return [
            'search_console' => ['enabled' => (bool) ($searchConsole['enabled'] ?? false), 'configured' => filled($searchConsole['site_url'] ?? null), 'ready' => false, 'safe_message' => 'Bağlantı ve service-account credentialı bekleniyor.'],
            'indexnow' => ['enabled' => (bool) ($indexNow['enabled'] ?? false), 'configured' => filled(config('app.url')), 'ready' => false, 'safe_message' => 'Domain ve public key dosyası doğrulaması bekleniyor.'],
            'x_distribution' => ['enabled' => (bool) ($social['x_enabled'] ?? false), 'configured' => false, 'ready' => false, 'safe_message' => 'Yalnız taslak modu kullanılabilir; gerçek paylaşım kapalı.'],
        ];
    }
}
