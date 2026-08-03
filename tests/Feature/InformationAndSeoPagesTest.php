<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InformationAndSeoPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_information_pages_render_in_turkish(): void
    {
        foreach (['hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'gorsel-ve-kaynak-politikasi', 'iletisim'] as $path) {
            $this->get('/'.$path)->assertOk()->assertSee('House Chef Cyprus');
        }
    }

    public function test_legal_information_center_is_canonical_and_legacy_pages_redirect_to_their_sections(): void
    {
        $this->get(route('legal.center'))
            ->assertOk()
            ->assertSee('House Chef Cyprus Kullanım Koşulları, Gizlilik, Çerez ve İçerik Politikası')
            ->assertSee('Son güncelleme: 3 Ağustos 2026')
            ->assertSee('Tarif ve Sağlık Uyarıları')
            ->assertSee('Görsel ve Yapay Zekâ Politikası')
            ->assertSee('data-legal-copy-status', false)
            ->assertSee('brand/house-chef-cyprus-logo-on-light.svg', false);

        $this->get('/kullanim-kosullari')->assertRedirect('/yasal-bilgilendirme#kullanim-kosullari');
        $this->get('/gizlilik')->assertRedirect('/yasal-bilgilendirme#gizlilik-ve-kisisel-veriler');
        $this->get('/cerezler')->assertRedirect('/yasal-bilgilendirme#cerez-politikasi');
        $this->get('/cerez-politikasi')->assertRedirect('/yasal-bilgilendirme#cerez-politikasi');
        $this->get('/kaynak-politikasi')->assertRedirect('/yasal-bilgilendirme#icerik-ve-kaynak-politikasi');
        $this->get('/kaynak-ve-veri-guven-politikasi')->assertRedirect('/yasal-bilgilendirme#icerik-ve-kaynak-politikasi');
    }

    public function test_robots_disallows_private_areas_and_sitemap_only_lists_public_verified_recipes(): void
    {
        $this->seed(HouseChefSeeder::class);
        $public = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->firstOrFail();

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /yonetim/')->assertSee('Sitemap:');
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/sitemaps/recipes.xml');
        $this->get('/sitemaps/recipes.xml')->assertOk()->assertSee($public->slug);
    }

    public function test_auth_and_missing_pages_are_not_indexable_and_missing_page_has_safe_navigation(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="robots" content="noindex,nofollow"', false);
        $this->get('/profil-olmayan-v1-rc-sayfa')
            ->assertNotFound()
            ->assertSee('Aradığınız sayfa burada değil.')
            ->assertSee('Ana sayfaya dön')
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }
}
