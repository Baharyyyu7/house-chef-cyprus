<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * robots.txt tek grup ve tek kaynak; /index.php ana sayfanın kopyası değil.
 *
 * 8 Ekim 2026 denetimi: robots.txt'deki isimli bot grupları (GPTBot,
 * Bingbot ...) "*" grubundaki /yonetim ve /favoriler yasağını eziyordu:
 * bir bot kendi adını taşıyan grubu bulursa "*" grubunu hiç okumaz.
 * Host ve Key satırları robots standardında yok. /index.php ana sayfanın
 * indekslenebilir bir kopyasıydı.
 */
final class RobotsAndIndexPhpDuplicateTest extends TestCase
{
    public function test_robots_yalniz_yildiz_grubu_ve_site_haritasi_tasir(): void
    {
        $robots = (string) file_get_contents(public_path('robots.txt'));

        preg_match_all('/^User-agent:\s*(.+)$/mi', $robots, $gruplar);
        $this->assertSame(['*'], array_map('trim', $gruplar[1]));
        $this->assertDoesNotMatchRegularExpression('/^(Host|Key|Key-Location|IndexNow):/mi', $robots);
        $this->assertStringContainsString('Disallow: /favoriler', $robots);
        $this->assertStringContainsString('Disallow: /yonetim/', $robots);
        $this->assertMatchesRegularExpression('#^Sitemap: https://housechefcyprus\.com/sitemap\.xml$#m', $robots);
    }

    public function test_olu_robots_sablonu_ve_rotasi_yok(): void
    {
        $this->assertFalse(Route::has('robots'));
        $this->assertFileDoesNotExist(resource_path('views/robots.blade.php'));
    }

    public function test_dogrudan_index_php_istegi_kok_adrese_kalici_yonlendirilir(): void
    {
        $htaccess = (string) file_get_contents(public_path('.htaccess'));

        $kural = strpos($htaccess, 'RewriteRule ^index\.php(?:/(.*))?$ /$1 [L,R=301]');
        $onDenetleyici = strpos($htaccess, 'RewriteRule ^ index.php [L]');
        $this->assertNotFalse($kural);
        $this->assertNotFalse($onDenetleyici);
        $this->assertLessThan($onDenetleyici, $kural, 'Yönlendirme ön denetleyici kuralından önce gelmeli.');

        // Koşul ham istek satırına (THE_REQUEST) bakar: ön denetleyicinin iç
        // yeniden yazımı onu değiştirmez, döngü olmaz. Apache 2.4 ile de
        // denendi: /index.php, /index.php?a=1 ve /index.php/x 301; /, /x 200.
        $this->assertMatchesRegularExpression('/RewriteCond %\{THE_REQUEST\} \^\[A-Z\]\+\\\\s\/index\\\\\.php/', $htaccess);
        $kosul = '/^[A-Z]+\s\/index\.php(?:[\/?\s])/i';
        $this->assertMatchesRegularExpression($kosul, 'GET /index.php HTTP/1.1');
        $this->assertMatchesRegularExpression($kosul, 'GET /index.php?a=1 HTTP/1.1');
        $this->assertDoesNotMatchRegularExpression($kosul, 'GET / HTTP/1.1');
        $this->assertDoesNotMatchRegularExpression($kosul, 'GET /index.phpx HTTP/1.1');
    }
}
