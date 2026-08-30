<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Integrity\TranslationReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Çeviri hazırlığı.
 *
 * Site dört dil için hazır ama hiç çeviri yok ve dillerin dördü de kapalı.
 * Tehlike sıralamada: Türkçe tarafı eksik bir tarifi çevirmek hatayı dille
 * çarpar. Yayındaki tariflerin yarısına yakınının onaylı görseli yok;
 * bunları dört dile çevirmek yüzlerce zayıf sayfa demek ve bu site zaten
 * bir kez ölçekli zayıf içerik yüzünden ceza yedi.
 */
final class TranslationReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_complete_recipe_is_ready(): void
    {
        $this->tarif('Tam Tarif', gorsel: true);

        $rapor = $this->rapor();

        $this->assertSame(1, $rapor['ready']);
        $this->assertSame(0, $rapor['blocked']);
    }

    public function test_a_recipe_without_an_approved_image_is_blocked(): void
    {
        // Üretimde baskın engel bu: çevirmeden önce görselin tamamlanması
        // gerekiyor, yoksa aynı eksik sayfa dört dilde çoğalır.
        $this->tarif('Görselsiz Tarif', gorsel: false);

        $this->assertContains('onayli_gorsel_yok', $this->engeller());
    }

    public function test_a_thin_description_blocks_translation(): void
    {
        $this->tarif('İnce Açıklamalı', gorsel: true, aciklama: 'Kısa.');

        $this->assertContains('aciklama_ince', $this->engeller());
    }

    public function test_a_placeholder_ingredient_blocks_translation(): void
    {
        $this->tarif('Yer Tutuculu', gorsel: true, malzeme: 'Ana Malzeme');

        $this->assertContains('yer_tutucu_malzeme', $this->engeller());
    }

    public function test_a_recipe_without_steps_is_blocked(): void
    {
        $tarif = $this->tarif('Adımsız', gorsel: true);
        $tarif->steps()->delete();

        $this->assertContains('adim_yok', $this->engeller());
    }

    public function test_the_queue_only_contains_ready_recipes(): void
    {
        $this->tarif('Hazır Tarif', gorsel: true);
        $this->tarif('Hazır Değil', gorsel: false);

        $sira = collect($this->rapor()['queue'])->pluck('title');

        $this->assertContains('Hazır Tarif', $sira);
        $this->assertNotContains('Hazır Değil', $sira);
    }

    public function test_blockers_are_counted_so_the_bottleneck_is_visible(): void
    {
        // Rapor "kaç tanesi hazır değil" demekle kalmamalı; hangi engelin
        // baskın olduğunu göstermeli, sıralama kararı ona göre veriliyor.
        $this->tarif('Bir', gorsel: false);
        $this->tarif('İki', gorsel: false);
        $this->tarif('Üç', gorsel: true, aciklama: 'Kısa.');

        $engeller = $this->rapor()['blockers'];

        $this->assertSame(2, $engeller['onayli_gorsel_yok']);
        $this->assertSame(1, $engeller['aciklama_ince']);
    }

    public function test_the_report_states_its_own_status(): void
    {
        // Koşu geçmişi dört muhafızı da aynı biçimde kaydediyor; bunun için
        // her raporun durumunu kendisinin söylemesi gerekiyor.
        $this->tarif('Engelli', gorsel: false);

        $this->assertSame('blocked', $this->rapor()['status']);
    }

    public function test_a_fully_ready_catalogue_reports_ready(): void
    {
        $this->tarif('Hazır', gorsel: true);

        $this->assertSame('ready', $this->rapor()['status']);
    }

    /** @return array<string, mixed> */
    private function rapor(): array
    {
        return app(TranslationReadiness::class)->report();
    }

    /** @return list<string> */
    private function engeller(): array
    {
        return array_keys($this->rapor()['blockers']);
    }

    private function tarif(
        string $baslik,
        bool $gorsel,
        string $aciklama = 'Bu tarif için yeterince uzun, gerçek bir açıklama metni burada duruyor; malzemenin nasıl hazırlandığını ve neden o sırayla pişirildiğini anlatıyor, böylece eşiği rahatça aşıyor.',
        string $malzeme = 'Un',
    ): Recipe {
        $tarif = Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => $aciklama,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $kayit = Ingredient::query()->firstOrCreate(['name' => $malzeme], ['slug' => Str::slug($malzeme).'-'.uniqid()]);
        $tarif->recipeIngredients()->create([
            'ingredient_id' => $kayit->id,
            'display_quantity' => 1,
            'display_measure_code' => 'g',
            'sort_order' => 1,
        ]);

        $tarif->steps()->create(['instruction' => 'Pişirin.', 'title' => 'Adım', 'position' => 1, 'sort_order' => 1]);

        if ($gorsel) {
            $tarif->media()->create([
                'kind' => 'recipe_synthetic',
                'disk' => 'public',
                'path' => 'media/approved-synthetic/recipe/ABC/source.jpg',
                'mime_type' => 'image/jpeg',
                'visibility' => 'public',
                'media_status' => 'approved_real_media',
            ]);
        }

        return $tarif->load(['media', 'recipeIngredients.ingredient', 'steps']);
    }
}
