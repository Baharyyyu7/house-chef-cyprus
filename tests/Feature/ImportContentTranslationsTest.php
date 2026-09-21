<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImportContentTranslationsTest extends TestCase
{
    use RefreshDatabase;

    private string $klasor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->klasor = storage_path('app/content-import/translations/en');

        if (! is_dir($this->klasor)) {
            mkdir($this->klasor, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->klasor.'/*') ?: [] as $dosya) {
            @unlink($dosya);
        }

        @rmdir($this->klasor);
        @rmdir(dirname($this->klasor));

        parent::tearDown();
    }

    public function test_report_mode_is_the_default_and_writes_nothing(): void
    {
        $tarif = $this->tarif();
        $this->dosyaYaz('recipe-hellim-izgara.json', $this->tamCeviri());

        $this->artisan('content:import-translations')->assertExitCode(0);

        $this->assertSame(0, $tarif->translations()->count());
    }

    public function test_a_complete_translation_is_imported(): void
    {
        $tarif = $this->tarif();
        $this->dosyaYaz('recipe-hellim-izgara.json', $this->tamCeviri());

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertTrue($tarif->fresh()->hasTranslation('en'));
        $this->assertSame('Grilled Halloumi', $tarif->fresh()->translated('title', 'en'));
    }

    public function test_a_partial_translation_is_refused(): void
    {
        // Başlığı İngilizce, açıklaması Türkçe bir sayfa çevrilmemiş sayfanın
        // kendisidir; yarım kayıt yazmak durumu gizler.
        $tarif = $this->tarif();
        $veri = $this->tamCeviri();
        unset($veri['fields']['description']);
        $this->dosyaYaz('recipe-hellim-izgara.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])
            ->expectsOutputToContain('eksik alan')
            ->assertExitCode(0);

        $this->assertSame(0, $tarif->fresh()->translations()->count());
    }

    public function test_a_translation_for_another_locale_is_refused(): void
    {
        $tarif = $this->tarif();
        $veri = $this->tamCeviri();
        $veri['locale'] = 'pl';
        $this->dosyaYaz('recipe-hellim-izgara.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(0, $tarif->fresh()->translations()->count());
    }

    public function test_a_translation_without_a_locale_is_refused(): void
    {
        $tarif = $this->tarif();
        $veri = $this->tamCeviri();
        unset($veri['locale']);
        $this->dosyaYaz('recipe-hellim-izgara.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(0, $tarif->fresh()->translations()->count());
    }

    public function test_copying_the_source_text_is_refused(): void
    {
        // Türkçe metnin kopyalanması alanı doluymuş gibi gösterir ama sayfa
        // Türkçe kalır - engellemeye çalıştığımız durumun ta kendisi.
        $tarif = $this->tarif();
        $veri = $this->tamCeviri();
        $veri['fields']['title'] = 'Hellim Izgara';
        $this->dosyaYaz('recipe-hellim-izgara.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])
            ->expectsOutputToContain('kaynak metnin kopyası')
            ->assertExitCode(0);

        $this->assertSame(0, $tarif->fresh()->translations()->count());
    }

    public function test_php_files_are_never_executed(): void
    {
        $isaret = storage_path('translation-import-rce-marker');
        @unlink($isaret);

        file_put_contents(
            $this->klasor.'/kotu.php',
            "<?php file_put_contents('".$isaret."', 'unsafe'); return [];\n"
        );

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertFileDoesNotExist($isaret);
    }

    public function test_importing_a_translation_never_enables_the_locale(): void
    {
        config()->set('locales.supported.en.enabled', false);
        // Dil etkinleştirmesi ayrı ve insana ait bir karar.
        $this->tarif();
        $this->dosyaYaz('recipe-hellim-izgara.json', $this->tamCeviri());

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertFalse(config('locales.supported.en.enabled'));
    }

    public function test_slug_with_public_id_suffix_is_resolved(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Cennet Çamuru',
            'slug' => 'kilis-cennet-camuru-WTQMM',
            'excerpt' => 'Kilis tatlısı.',
            'description' => 'Geleneksel cennet çamuru tatlısı.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $veri = [
            'type' => 'recipe',
            'slug' => 'kilis-cennet-camuru',
            'locale' => 'en',
            'fields' => [
                'title' => 'Cennet Camuru Dessert',
                'excerpt' => 'Traditional shredded pastry with pistachios.',
                'description' => 'A decadent dessert from Kilis.',
            ],
        ];
        $this->dosyaYaz('recipe-kilis-cennet-camuru.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertTrue($tarif->fresh()->hasTranslation('en'));
        $this->assertSame('Cennet Camuru Dessert', $tarif->fresh()->translated('title', 'en'));
    }

    public function test_slug_redirect_is_resolved(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Etsiz Çiğ Köfte',
            'slug' => 'etsiz-cig-kofte-A96HF',
            'excerpt' => 'Etsiz lezzet.',
            'description' => 'Urfa usulü etsiz çiğ köfte.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        \App\Models\SlugRedirect::query()->create([
            'model_type' => Recipe::class,
            'model_id' => $tarif->id,
            'old_slug' => 'cig-kofte-A96HF',
        ]);

        $veri = [
            'type' => 'recipe',
            'slug' => 'cig-kofte-A96HF',
            'locale' => 'en',
            'fields' => [
                'title' => 'Vegetarian Cig Kofte',
                'excerpt' => 'Spiced bulgur patties.',
                'description' => 'Authentic meatless bulgur balls.',
            ],
        ];
        $this->dosyaYaz('recipe-cig-kofte.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertTrue($tarif->fresh()->hasTranslation('en'));
        $this->assertSame('Vegetarian Cig Kofte', $tarif->fresh()->translated('title', 'en'));
    }

    public function test_geo_prefix_slug_is_resolved(): void
    {
        $post = \App\Models\BlogPost::query()->create([
            'title' => 'Karpaz Dağ Kekiği',
            'slug' => 'karpaz-dag-kekigi-XYZ89',
            'excerpt' => 'Karpaz kekik rehberi.',
            'body' => 'Karpaz yarımadasının zengin yabani dağ kekiği gastronomi mirası...',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $veri = [
            'type' => 'blog',
            'slug' => 'geo-2026-karpaz-dag-kekigi',
            'locale' => 'en',
            'fields' => [
                'title' => 'Karpaz Mountain Thyme Guide',
                'excerpt' => 'Wild Mediterranean herbs of Cyprus.',
                'body' => 'Comprehensive culinary exploration of wild thyme from Karpaz...',
            ],
        ];
        $this->dosyaYaz('blog-geo-karpaz.json', $veri);

        $this->artisan('content:import-translations', ['--apply' => true])->assertExitCode(0);

        $this->assertTrue($post->fresh()->hasTranslation('en'));
        $this->assertSame('Karpaz Mountain Thyme Guide', $post->fresh()->translated('title', 'en'));
    }

    /** @return array<string, mixed> */
    private function tamCeviri(): array
    {
        return [
            'type' => 'recipe',
            'slug' => 'hellim-izgara',
            'locale' => 'en',
            'fields' => [
                'title' => 'Grilled Halloumi',
                'excerpt' => 'The staple of the Cypriot table.',
                'description' => 'Halloumi that grills without falling apart.',
            ],
        ];
    }

    /** @param array<string, mixed> $veri */
    private function dosyaYaz(string $ad, array $veri): void
    {
        file_put_contents($this->klasor.'/'.$ad, (string) json_encode($veri, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Hellim Izgara',
            'slug' => 'hellim-izgara',
            'excerpt' => 'Kıbrıs sofrasının vazgeçilmezi.',
            'description' => 'Izgarada dağılmadan kızaran hellim.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
