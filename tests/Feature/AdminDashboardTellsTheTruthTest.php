<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Services\DataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Panelin "Dikkat gerekenler" bölümü yalnız açılmış DataQualityIssue kayıtlarına
 * bakıyordu. Kimse kayıt açmadığı sürece 678 tarif aynı 49 fotoğrafı paylaşırken
 * bile panel "açık dikkat kaydı yok" ve "%100 veri kalitesi" diyordu.
 */
final class AdminDashboardTellsTheTruthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_recipes_that_share_another_recipes_photo(): void
    {
        $paylasilan = 'media/approved-synthetic/recipe/PAYLASILAN/source.jpg';
        $this->publishedRecipeWithMedia('birinci-tarif', $paylasilan);
        $this->publishedRecipeWithMedia('ikinci-tarif', $paylasilan);
        $this->publishedRecipeWithMedia('ucuncu-tarif', 'media/approved-synthetic/recipe/TEKIL/source.jpg');

        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Aynı görsel birden fazla tarifte', false)
            // İki tarif aynı fotoğrafı gösteriyor; hangisinin yanlış olduğu
            // bilinemeyeceği için ikisi de sayılıyor. Önceki ifade "1 tarif,
            // başka bir tarife ait fotoğrafı gösteriyor" diyordu ve bu sayım
            // aynı tarifin mükerrer kaydını da yanlış fotoğraf sayıyordu.
            ->assertSee('2 tarif aynı fotoğrafı paylaşıyor', false);
    }

    public function test_it_stays_quiet_when_every_recipe_has_its_own_photo(): void
    {
        $this->publishedRecipeWithMedia('birinci-tarif', 'media/approved-synthetic/recipe/BIR/source.jpg');
        $this->publishedRecipeWithMedia('ikinci-tarif', 'media/approved-synthetic/recipe/IKI/source.jpg');

        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Aynı görsel birden fazla tarifte', false);
    }

    public function test_it_reports_published_recipes_without_approved_media(): void
    {
        Recipe::query()->create(['title' => 'Görselsiz', 'slug' => 'gorselsiz', 'status' => 'published', 'verification_status' => 'verified']);

        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Onaylı görseli olmayan yayındaki tarif', false);
    }

    public function test_reference_numbers_no_longer_repeat_the_empty_comparison_notice(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Karşılaştırma için yeterli veri yok', false);
    }

    public function test_it_does_not_claim_a_perfect_quality_score_before_any_scan_has_run(): void
    {
        // Skor "100 - açık bulgu cezası" olduğu için tarama hiç çalışmadığında
        // her kategori %100 çıkar. Bu sayı sağlık değil, bilgisizliktir.
        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kalite taraması henüz çalıştırılmadı', false)
            ->assertDontSee('Kaynak / provenance', false);
    }

    public function test_it_shows_the_category_scores_once_a_scan_has_run(): void
    {
        app(DataQualityService::class)->scan();
        // Önbellek her deploy'da temizlenir; taranmış bir katalog bu yüzden
        // "hiç bakılmadı" görünmemeli.
        Cache::flush();

        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kaynak / provenance', false)
            ->assertDontSee('Kalite taraması henüz çalıştırılmadı', false);
    }

    private function publishedRecipeWithMedia(string $slug, string $path): Recipe
    {
        $recipe = Recipe::query()->create(['title' => Str::headline($slug), 'slug' => $slug, 'status' => 'published', 'verification_status' => 'verified']);

        $recipe->media()->create([
            'public_id' => (string) Str::ulid(),
            'kind' => 'image',
            'disk' => 'public',
            'path' => $path,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
        ]);

        return $recipe;
    }

    private function actingAsAdmin(): self
    {
        return $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]));
    }
}
