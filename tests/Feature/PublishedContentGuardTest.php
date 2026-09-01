<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Integrity\PublishedContentGuard;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Yayındaki içeriği bilinen bozulma desenlerine karşı tarayan nöbetçi.
 *
 * Bu sitede arıza hep aynı biçimde geldi: bozuk içerik sessizce yayına
 * girdi ve haftalarca fark edilmedi. Nöbetçinin işi bunu bir dahaki sefere
 * ilk gün yakalamak.
 */
final class PublishedContentGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_clean_catalogue_reports_clean(): void
    {
        $this->tarif('Mercimek Çorbası', ['Kırmızı Mercimek', 'Kuru Soğan'], 'Mercimeği yıkayın ve haşlayın.');

        $this->assertSame('clean', $this->tara()['status']);
    }

    public function test_a_placeholder_ingredient_is_caught(): void
    {
        // Üretimde 34 tarifin malzeme listesi birebir aynıydı:
        // "500 g Ana Malzeme" artı zeytinyağı, soğan, sarımsak, tuz, karabiber.
        $this->tarif('Ev Kumpiri', ['Ana Malzeme', 'Zeytinyağı'], 'Patatesi fırınlayın.');

        $this->assertContains('ev-kumpiri', $this->slugler('placeholder_ingredients'));
    }

    public function test_placeholder_steps_are_caught(): void
    {
        $this->tarif(
            'Tas Kebabı',
            ['Dana Kuşbaşı Et'],
            'Malzemeleri hazırlayın ve temizleyin. Uygun şekilde doğrayıp karıştırın. Gereken şekilde pişirin.'
        );

        $this->assertContains('tas-kebabi', $this->slugler('placeholder_steps'));
    }

    public function test_a_title_that_contradicts_the_ingredients_is_caught(): void
    {
        // "Mercimek Çorbası" kırmızı et ve balık istiyordu.
        $this->tarif('Mercimek Çorbası', ['Kırmızı Et', 'Balık'], 'Eti haşlayın.');

        $this->assertContains('mercimek-corbasi', $this->slugler('title_ingredient_mismatch'));
    }

    public function test_a_matching_ingredient_is_not_flagged(): void
    {
        // Daralma doğru tarifleri elemeyi amaçlamıyor.
        $this->tarif('Mercimek Çorbası', ['Kırmızı Mercimek', 'Havuç'], 'Mercimeği haşlayın.');

        $this->assertSame([], $this->slugler('title_ingredient_mismatch'));
    }

    public function test_a_blog_post_that_skipped_the_editorial_gate_is_caught(): void
    {
        BlogPost::query()->create([
            'title' => 'Kapıdan Geçmemiş Yazı',
            'slug' => 'kapidan-gecmemis',
            'body' => 'Metin.',
            'excerpt' => 'Özet.',
            'status' => 'published',
            'editorial_status' => 'approved',
            'fact_check_status' => 'pending',
            'originality_status' => 'pending',
        ]);

        $this->assertContains('kapidan-gecmemis', $this->slugler('blog_missing_editorial_gate'));
    }

    public function test_reporting_never_changes_anything_by_default(): void
    {
        // Varsayılan davranış yalnız bakmak. Sessizce içerik kaldıran bir
        // nöbetçi, yakaladığı sorundan daha tehlikeli olurdu.
        $tarif = $this->tarif('Ev Kumpiri', ['Ana Malzeme'], 'Fırınlayın.');

        $this->tara();

        $this->assertSame('published', $tarif->fresh()->status);
    }

    public function test_enforcing_withdraws_a_broken_recipe_without_deleting_it(): void
    {
        $tarif = $this->tarif('Ev Kumpiri', ['Ana Malzeme'], 'Fırınlayın.');

        $rapor = $this->tara(true);

        $this->assertSame('draft', $tarif->fresh()->status);
        $this->assertContains('ev-kumpiri', $rapor['withdrawn']);
        $this->assertNotNull(Recipe::query()->where('slug', 'ev-kumpiri')->first());
    }

    public function test_enforcing_never_touches_blog_posts(): void
    {
        // Blog tarafı editoryal karar; nöbetçi onu yayından almaz.
        $yazi = BlogPost::query()->create([
            'title' => 'Kapıdan Geçmemiş Yazı',
            'slug' => 'kapidan-gecmemis',
            'body' => 'Metin.',
            'excerpt' => 'Özet.',
            'status' => 'published',
            'editorial_status' => 'approved',
            'fact_check_status' => 'pending',
            'originality_status' => 'pending',
        ]);

        $this->tara(true);

        $this->assertSame('published', $yazi->fresh()->status);
    }

    public function test_the_guard_runs_on_the_schedule(): void
    {
        $olay = collect(app(Schedule::class)->events())
            ->first(fn ($e): bool => $e->description === 'content_integrity_guard');

        $this->assertNotNull($olay, 'Nöbetçi zamanlamaya bağlı olmalı; elle koşturulan bir kontrol unutulur.');
    }

    /** @return array<string, mixed> */
    private function tara(bool $enforce = false): array
    {
        return app(PublishedContentGuard::class)->scan($enforce);
    }

    /** @return list<string> */
    private function slugler(string $tur): array
    {
        return $this->tara()['findings'][$tur];
    }

    /** @param list<string> $malzemeler */
    private function tarif(string $baslik, array $malzemeler, string $adim): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        foreach ($malzemeler as $sira => $ad) {
            $malzeme = Ingredient::query()->firstOrCreate(['name' => $ad], ['slug' => Str::slug($ad).'-'.uniqid()]);
            $tarif->recipeIngredients()->create([
                'ingredient_id' => $malzeme->id,
                'display_quantity' => 1,
                'display_measure_code' => 'g',
                'sort_order' => $sira + 1,
            ]);
        }

        $tarif->steps()->create(['instruction' => $adim, 'title' => 'Adım', 'position' => 1, 'sort_order' => 1]);

        return $tarif;
    }

    public function test_a_thin_published_post_is_reported(): void
    {
        // Bu site bir kez ölçekli ince içerik yüzünden ceza yedi. Az sayıda
        // zayıf sayfa güçlü sayfaların sıralamasını da aşağı çeker.
        BlogPost::query()->create([
            'title' => 'Kısa Yazı',
            'slug' => 'kisa-yazi',
            'body' => str_repeat('kısa metin ', 30),
            'excerpt' => 'Özet.',
            'status' => 'published',
            'editorial_status' => 'approved',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
        ]);

        $this->assertContains('kisa-yazi', $this->slugler('thin_blog_posts'));
    }

    public function test_a_substantial_post_is_not_reported(): void
    {
        BlogPost::query()->create([
            'title' => 'Dolu Yazı',
            'slug' => 'dolu-yazi',
            'body' => str_repeat('Bu yazı konusunu gerçekten taşıyor ve yeterince uzun. ', 60),
            'excerpt' => 'Özet.',
            'status' => 'published',
            'editorial_status' => 'approved',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
        ]);

        $this->assertNotContains('dolu-yazi', $this->slugler('thin_blog_posts'));
    }

    public function test_a_draft_post_is_not_reported(): void
    {
        // Yayında olmayan yazı ziyaretçiye de arama motoruna da görünmüyor.
        BlogPost::query()->create([
            'title' => 'Taslak',
            'slug' => 'taslak-yazi',
            'body' => 'Çok kısa.',
            'excerpt' => 'Özet.',
            'status' => 'draft',
            'editorial_status' => 'approved',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
        ]);

        $this->assertNotContains('taslak-yazi', $this->slugler('thin_blog_posts'));
    }

    public function test_the_guard_reads_the_field_that_actually_holds_the_text(): void
    {
        // `content` diye bir sütun yok; alan `body`. Yanlış alanı okuyan bir
        // muhafız her yazıyı ince sayar ve hiçbir şey söylemez.
        $this->assertStringContainsString(
            'strip_tags((string) $yazi->body)',
            (string) file_get_contents(app_path('Services/Integrity/PublishedContentGuard.php'))
        );
    }
}
