<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class ContentTranslationGatesLocalePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    public function test_the_default_locale_needs_no_translation_rows(): void
    {
        $tarif = $this->tarif();

        $this->assertTrue($tarif->hasTranslation('tr'));
        $this->assertSame('Hellim Izgara', $tarif->translated('title', 'tr'));
    }

    public function test_a_record_without_a_translation_does_not_exist_in_that_locale(): void
    {
        // Sessizce Türkçeye düşmek en pahalı hata olurdu: /en/ altında Türkçe
        // gövde servis etmek, Google'ın sayfayı Türkçe sayması ve aynı
        // içeriğin ikinci kopyası olarak görmesi demek.
        $tarif = $this->tarif();

        $this->assertFalse($tarif->hasTranslation('en'));
        $this->assertNull($tarif->translated('title', 'en'));
    }

    public function test_a_partial_translation_is_not_enough(): void
    {
        // Başlığı çevrilmiş ama açıklaması Türkçe kalmış sayfa, çevrilmemiş
        // sayfanın kendisidir.
        $tarif = $this->tarif();
        $tarif->translations()->create(['locale' => 'en', 'field' => 'title', 'value' => 'Grilled Halloumi']);

        $this->assertFalse($tarif->fresh()->hasTranslation('en'));
    }

    public function test_a_fully_translated_record_exists_in_that_locale(): void
    {
        $tarif = $this->tamCeviri();

        $this->assertTrue($tarif->hasTranslation('en'));
        $this->assertSame('Grilled Halloumi', $tarif->translated('title', 'en'));
    }

    public function test_listing_scope_matches_the_same_definition(): void
    {
        // Listeleme, sitemap ve hreflang aynı tanımı paylaşmak zorunda; ayrı
        // tanımlar hreflang'in listede olmayan bir sayfayı göstermesine yol
        // açar ve küme geçersiz olur.
        $this->tarif('cevirisiz-tarif');
        $cevrili = $this->tamCeviri('cevrili-tarif');

        $sonuc = Recipe::query()->translatedInto('en')->pluck('id');

        $this->assertTrue($sonuc->contains($cevrili->id));
        $this->assertSame(1, $sonuc->count());
    }

    public function test_hreflang_omits_locales_where_the_page_does_not_exist(): void
    {
        $tarif = $this->tarif();
        $servis = app(LocaleUrlService::class);

        $kume = $servis->alternates(Request::create('/tarifler/hellim-izgara'), $tarif);

        $this->assertSame([], $kume, 'Çevirisi olmayan içerik için hreflang basılmamalı.');
    }

    public function test_hreflang_lists_both_locales_once_the_translation_exists(): void
    {
        $tarif = $this->tamCeviri();
        $servis = app(LocaleUrlService::class);

        $kume = collect($servis->alternates(Request::create('/tarifler/hellim-izgara'), $tarif))
            ->pluck('hreflang')->all();

        $this->assertContains('tr', $kume);
        $this->assertContains('en', $kume);
        $this->assertContains('x-default', $kume);
    }

    private function tarif(string $slug = 'hellim-izgara'): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Hellim Izgara',
            'slug' => $slug,
            'excerpt' => 'Kıbrıs sofrasının vazgeçilmezi.',
            'description' => 'Izgarada dağılmadan kızaran hellim.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    private function tamCeviri(string $slug = 'hellim-izgara'): Recipe
    {
        $tarif = $this->tarif($slug);

        foreach ([
            'title' => 'Grilled Halloumi',
            'excerpt' => 'The staple of the Cypriot table.',
            'description' => 'Halloumi that grills without falling apart.',
        ] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        return $tarif->fresh();
    }
}
