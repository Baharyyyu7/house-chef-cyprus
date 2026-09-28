<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AdminRecipeMediaDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_unique_public_recipes_instead_of_media_rows(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $withImage = $this->recipe('Görselli Tarif');
        $withoutImage = $this->recipe('Görselsiz Tarif');
        $this->eligibleMedia($withImage, $admin);
        $this->eligibleMedia($withImage, $admin);

        $hidden = Recipe::query()->create([
            'title' => 'Taslak Tarif', 'slug' => 'taslak-tarif', 'status' => 'draft',
            'verification_status' => 'verified',
        ]);
        $this->eligibleMedia($hidden, $admin);

        $response = $this->actingAs($admin)->get(route('admin.media.index'));

        $response->assertOk()
            ->assertSee('Yayındaki Tarifler')
            ->assertSee('Görselli Tarifler')
            ->assertSee('Görselsiz Tarifler')
            ->assertSee('Görselli Tarif')
            ->assertSee('Görselsiz Tarif')
            ->assertDontSee('Taslak Tarif')
            ->assertDontSee('Dosya kaydı var')
            ->assertDontSee('8.859');

        $this->assertSame(2, $response->viewData('sayilar')['total']);
        $this->assertSame(1, $response->viewData('sayilar')['with_image']);
        $this->assertSame(1, $response->viewData('sayilar')['without_image']);
    }

    private function recipe(string $title): Recipe
    {
        return Recipe::query()->create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    private function eligibleMedia(Recipe $recipe, User $admin): void
    {
        $id = (string) str()->ulid();
        $source = 'media/approved-synthetic/recipe/'.$recipe->public_id.'/'.$id.'.png';
        Storage::disk('public')->put($source, 'image');
        $variants = [];
        foreach (['thumbnail', 'square', 'card', 'open_graph', 'hero'] as $name) {
            $jpg = 'media/optimized/'.$id.'/'.$name.'.jpg';
            $webp = 'media/optimized/'.$id.'/'.$name.'.webp';
            Storage::disk('public')->put($jpg, 'x');
            Storage::disk('public')->put($webp, 'x');
            $variants[] = ['name' => $name, 'jpeg' => $jpg, 'webp' => $webp];
        }

        $recipe->media()->create([
            'public_id' => $id, 'kind' => 'recipe_synthetic', 'disk' => 'public', 'path' => $source,
            'mime_type' => 'image/png', 'visibility' => 'public', 'media_status' => 'approved_real_media',
            'verification_status' => 'verified', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 100,
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved',
            'media_reviewer_user_id' => $admin->id, 'media_reviewed_at' => now(),
            'approved_by_user_id' => $admin->id, 'approved_at' => now(),
            'responsive_variants' => $variants, 'checksum_sha256' => hash('sha256', $id),
        ]);
    }
}
