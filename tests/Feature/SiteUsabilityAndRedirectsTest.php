<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteUsabilityAndRedirectsTest extends TestCase
{
    use RefreshDatabase;
    public function test_common_and_friendly_paths_redirect_correctly_with_http_301(): void
    {
        $redirects = [
            '/admin' => '/yonetim',
            '/giris' => '/login',
            '/oturum-ac' => '/login',
            '/kayit' => '/register',
            '/uye-ol' => '/register',
            '/panel' => '/dashboard',
            '/profil' => '/profile',
            '/hesabim' => '/profile',
            '/yasal' => '/yasal-bilgilendirme',
            '/contact' => '/iletisim',
            '/recipes' => '/tarifler',
            '/about' => '/hakkimizda',
            '/privacy' => '/gizlilik',
            '/terms' => '/kullanim-kosullari',
            '/cookies' => '/cerezler',
            '/faq' => '/sss',
            '/pantry' => '/dolabim',
            '/dolap' => '/dolabim',
            '/favorites' => '/favoriler',
            '/koleksiyonlarim' => '/koleksiyonlar',
            '/pricing' => '/fiyatlandirma',
            '/shop' => '/magaza',
        ];

        foreach ($redirects as $from => $to) {
            $response = $this->get($from);
            $response->assertStatus(301);
            $response->assertRedirect($to);
        }
    }

    public function test_homepage_features_typing_search_input_and_explore_link(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $response->assertSee('id="hero-search-input"', false);
        $response->assertSee('name="q"', false);
        $response->assertSee('type="search"', false);
        $response->assertSee(route('ingredients.explore'), false);
    }

    public function test_public_header_includes_explore_link_for_all_visitors(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $this->assertGreaterThanOrEqual(2, substr_count((string) $response->getContent(), route('ingredients.explore')));
    }

    public function test_homepage_has_no_geo_meta_tags_but_keeps_enhanced_organization_schema(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        // geo.* ve ICBM kaldırıldı (8 Ekim 2026 denetimi): arama motorları
        // okumuyor ve her sayfayı Lefkoşa'da bir yer gibi işaretliyordu.
        $response->assertDontSee('name="geo.', false);
        $response->assertDontSee('name="ICBM"', false);
        $response->assertSee('areaServed', false);
        $response->assertSee('knowsAbout', false);
    }
}
