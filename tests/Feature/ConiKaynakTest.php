<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentTranslation;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * coni:kaynak Türkçe alanı yerinde düzeltir: adım kimliği ve çevirisi
 * kalır, eski değer geri alınabilir. content:import-recipes adımları
 * silip altı dilin çevirisini de götürüyordu.
 */
final class ConiKaynakTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Havuç Çorbası',
            'slug' => 'havuc-corbasi-test',
            'excerpt' => 'Rengiyle iştah açan havuç çorbası.',
            'description' => 'Krema ya da un yok; kıvam patatesten gelir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        foreach (['Sebzeleri doğrayın.', 'Üzerine krema veya dereotu ekleyerek servis yapın.'] as $i => $metin) {
            $adim = RecipeStep::query()->create(['recipe_id' => $tarif->id, 'position' => $i + 1, 'title' => 'Adım '.($i + 1), 'instruction' => $metin]);
            ContentTranslation::query()->create(['translatable_type' => $adim->getMorphClass(), 'translatable_id' => $adim->id, 'locale' => 'en', 'field' => 'instruction', 'value' => 'EN '.$i]);
        }

        return $tarif;
    }

    private function dosya(array $veri): string
    {
        $dosya = tempnam(sys_get_temp_dir(), 'coni-kaynak');
        file_put_contents($dosya, (string) json_encode($veri, JSON_UNESCAPED_UNICODE));

        return $dosya;
    }

    public function test_alanlari_yerinde_yazar_ve_geri_alir(): void
    {
        $tarif = $this->tarif();
        $adimId = $tarif->steps()->where('position', 2)->value('id');
        Queue::fake();
        $dosya = $this->dosya(['havuc-corbasi-test' => [
            'description' => 'Un kullanılmaz; kıvam patatesten gelir.',
            'steps' => ['2' => ['instruction' => 'Üzerine dereotu serperek servis yapın.', 'chef_tip' => 'İsterseniz bir kaşık krema ekleyin.']],
        ]]);

        $this->artisan('coni:kaynak', ['dosya' => $dosya])->expectsOutputToContain('1 tarifte 3 alan yazıldı')->assertSuccessful();

        $tarif->refresh();
        $adim = RecipeStep::query()->findOrFail($adimId);
        $this->assertSame('Un kullanılmaz; kıvam patatesten gelir.', $tarif->getRawOriginal('description'));
        $this->assertSame('Havuç Çorbası', $tarif->getRawOriginal('title'));
        $this->assertSame('Üzerine dereotu serperek servis yapın.', $adim->getRawOriginal('instruction'));
        $this->assertSame('EN 1', $adim->translated('instruction', 'en'));

        $rapor = IntegrityReport::query()->where('guard', 'coni:kaynak')->firstOrFail();
        $this->artisan('coni:kaynak', ['--geri-al' => $rapor->id])->assertSuccessful();

        $this->assertSame('Krema ya da un yok; kıvam patatesten gelir.', $tarif->fresh()->getRawOriginal('description'));
        $this->assertSame('Üzerine krema veya dereotu ekleyerek servis yapın.', $adim->fresh()->getRawOriginal('instruction'));
        $this->assertNull($adim->fresh()->getRawOriginal('chef_tip'));
        unlink($dosya);
    }

    public function test_olmayan_adim_hicbir_seyi_yazmaz(): void
    {
        $tarif = $this->tarif();
        $dosya = $this->dosya(['havuc-corbasi-test' => ['title' => 'Yeni Başlık', 'steps' => ['9' => ['instruction' => 'Yok.']]]]);

        $this->artisan('coni:kaynak', ['dosya' => $dosya])->assertFailed();

        $this->assertSame('Havuç Çorbası', $tarif->fresh()->getRawOriginal('title'));
        $this->assertSame(0, IntegrityReport::query()->where('guard', 'coni:kaynak')->count());
        unlink($dosya);
    }
}
