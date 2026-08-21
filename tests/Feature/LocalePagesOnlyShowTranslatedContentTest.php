<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocalePagesOnlyShowTranslatedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    public function test_the_turkish_site_is_unaffected(): void
    {
        // Varsayılan dil çeviri satırı istemez; kaynak sütunların kendisi
        // içeriktir. Kapsam Türkçe tarafı daraltmamalı.
        $tarif = $this->tarif();
        $yazi = $this->yazi();

        $this->get('/tarifler/'.$tarif->slug)->assertOk();
        $this->get('/blog/'.$yazi->slug)->assertOk();
        $this->get('/blog')->assertOk()->assertSee($yazi->title);
    }

    public function test_an_untranslated_recipe_has_no_address_in_that_locale(): void
    {
        // Sessizce Türkçe içerik servis etmek, hreflang'in gösterdiği adresin
        // başka dilde içerik döndürmesi demekti.
        $tarif = $this->tarif();

        $this->get('/en/tarifler/'.$tarif->slug)->assertNotFound();
    }

    public function test_an_untranslated_post_has_no_address_in_that_locale(): void
    {
        $yazi = $this->yazi();

        $this->get('/en/blog/'.$yazi->slug)->assertNotFound();
    }

    public function test_the_locale_listing_does_not_show_untranslated_records(): void
    {
        $yazi = $this->yazi();

        $this->get('/en/blog')->assertOk()->assertDontSee($yazi->title);
    }

    public function test_a_translated_record_is_reachable_and_listed(): void
    {
        $yazi = $this->yazi();

        foreach (['title' => 'Translated Post', 'excerpt' => 'Summary.', 'body' => 'Body text.'] as $alan => $deger) {
            $yazi->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        $this->get('/en/blog/'.$yazi->slug)->assertOk();
        $this->get('/en/blog')->assertOk();
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Hellim Izgara',
            'slug' => 'hellim-izgara-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    private function yazi(): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Çevirisiz Yazı Başlığı',
            'slug' => 'cevirisiz-yazi-'.uniqid(),
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
