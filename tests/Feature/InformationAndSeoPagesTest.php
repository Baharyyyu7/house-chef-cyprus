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
        foreach (['hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'gorsel-ve-kaynak-politikasi', 'gizlilik', 'kullanim-kosullari', 'cerez-politikasi', 'iletisim'] as $path) {
            $this->get('/'.$path)->assertOk()->assertSee('House Chef Cyprus');
        }
    }

    public function test_robots_disallows_private_areas_and_sitemap_only_lists_public_verified_recipes(): void
    {
        $this->seed(HouseChefSeeder::class);
        $public = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->firstOrFail();

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /yonetim/')->assertSee('Sitemap:');
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee($public->slug);
    }
}
