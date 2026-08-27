<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class RecipeMediaTruthfulnessPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_branded_fallback_is_never_presented_as_approved_food_photography(): void
    {
        $this->seed(HouseChefSeeder::class);
        $recipe = Recipe::query()->where('status', 'published')->firstOrFail();

        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('Yemek fotoğrafı hazırlanıyor')
            ->assertSee('Onaylı, tarife özel görsel bulunana kadar fotoğraf gösterilmez.')
            ->assertDontSee('için temsili yemek görseli')
            ->assertSee('İnceleme sürüyor');
        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('Onaylı yemek fotoğrafı henüz yok')
            ->assertSee('yer tutucu görsel yemek fotoğrafı olarak gösterilmez')
            ->assertDontSee('✓ Kayıt doğrulandı');
    }

    public function test_approved_synthetic_media_is_disclosed_in_recipe_and_blog_cards(): void
    {
        Storage::fake('public');
        $recipe = Recipe::query()->create([
            'title' => 'Sentetik görsel test tarifi',
            'slug' => 'sentetik-gorsel-test-tarifi',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
        $recipe->media()->create($this->approvedSyntheticMedia('recipe_synthetic', 'media/test/recipe.jpg', 'Test yemeği'));

        $post = BlogPost::query()->create([
            'title' => 'Sentetik görsel test yazısı',
            'slug' => 'sentetik-gorsel-test-yazisi',
            'excerpt' => 'Özet',
            'body' => 'Özgün içerik.',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $media = $post->media()->create($this->approvedSyntheticMedia('blog_synthetic', 'media/test/blog.jpg', 'Test illüstrasyonu'));
        $post->update(['hero_media_id' => $media->id]);

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('Yapay zekâ ile üretilmiş, editoryal olarak onaylanmış görsel.')
            ->assertSee('gerçek fotoğraf olarak sunulmaz');

        // Uyarı eskiden yalnız listedeydi; liste görselsiz olunca uyarı da
        // yok olmuştu. Görsel artık detayda duruyor, uyarı da orada.
        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('Yapay zekâ görseli')
            ->assertSee('Yapay zekâ ile üretilmiş editoryal illüstrasyon: Test illüstrasyonu');
    }

    public function test_homepage_featured_query_keeps_all_fields_required_by_the_public_media_gate(): void
    {
        Storage::fake('public');
        $recipe = Recipe::query()->create([
            'title' => 'Ana sayfa görsel kapısı tarifi',
            'slug' => 'ana-sayfa-gorsel-kapisi-tarifi',
            'excerpt' => 'Ana sayfa kartındaki onaylı görseli doğrulayan tarif.',
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'rest_minutes' => 0,
            'servings' => 4,
            'difficulty' => 'Kolay',
            'status' => 'published',
            'verification_status' => 'verified',
            'is_featured' => true,
            'published_at' => now(),
        ]);
        $recipe->media()->create($this->approvedSyntheticMedia('recipe_synthetic', 'media/test/homepage.jpg', 'Ana sayfa test yemeği'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ana sayfa görsel kapısı tarifi')
            ->assertSee('Yapay zekâ görseli')
            ->assertDontSee('Yemek fotoğrafı hazırlanıyor');
    }

    /** @return array<string,mixed> */
    private function approvedSyntheticMedia(string $kind, string $path, string $altText): array
    {
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        Storage::disk('public')->put($path, 'synthetic source');
        $variants = collect(['thumbnail', 'square', 'card', 'open_graph', 'hero'])->map(function (string $profile) use ($path): array {
            $prefix = str($path)->beforeLast('.')->toString().'-'.$profile;
            $jpeg = $prefix.'.jpg';
            $webp = $prefix.'.webp';
            Storage::disk('public')->put($jpeg, 'jpeg');
            Storage::disk('public')->put($webp, 'webp');

            return ['name' => $profile, 'width' => 1200, 'height' => 800, 'jpeg' => $jpeg, 'webp' => $webp];
        })->all();

        return [
            'kind' => $kind,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 1,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
            'generated_by_ai' => true,
            'ai_review_status' => 'SYNTHETIC_APPROVED',
            'alt_text' => $altText,
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 95,
            'responsive_variants' => $variants,
            'media_reviewer_user_id' => $reviewer->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $reviewer->id,
            'approved_at' => now(),
        ];
    }
}
