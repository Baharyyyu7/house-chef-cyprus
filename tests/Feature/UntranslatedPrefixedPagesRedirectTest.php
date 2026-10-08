<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Çevirisi olmayan sayfanın dil önekli adresi Türkçe sürüme 301 verir.
 *
 * 8 Ekim 2026 denetimi: /en/hakkimizda, /de/sss, /de/yasal-bilgilendirme
 * gibi 74 adres Türkçe içerik servis ediyordu; kanoniği Türkçe sürümdü ama
 * hreflang onları ayrı dil sürümü gibi ilan ediyordu.
 */
final class UntranslatedPrefixedPagesRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_cevirisi_olmayan_statik_sayfa_turkce_surume_kalici_yonlendirilir(): void
    {
        foreach (['/en/hakkimizda' => '/hakkimizda', '/de/sss' => '/sss', '/de/yasal-bilgilendirme' => '/yasal-bilgilendirme', '/ru/iletisim?konu=a' => '/iletisim?konu=a'] as $onekli => $turkce) {
            $this->get($onekli)->assertStatus(301)->assertRedirect(url($turkce));
        }

        $this->get('/hakkimizda')->assertOk();
    }

    public function test_cevirisi_olan_sayfalar_onekli_adreste_kalir(): void
    {
        $this->get('/en')->assertOk();
        $this->get('/en/recipes')->assertOk();
        $this->get('/de/blog')->assertOk();
    }
}
