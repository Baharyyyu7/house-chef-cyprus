<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('1 tarif, başka bir tarife ait fotoğrafı gösteriyor', false);
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
