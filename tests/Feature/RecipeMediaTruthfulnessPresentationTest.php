<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Yapay zekâ görseli')
            ->assertSee('Yapay zekâ ile üretilmiş editoryal illüstrasyon: Test illüstrasyonu');
    }

    /** @return array<string,mixed> */
    private function approvedSyntheticMedia(string $kind, string $path, string $altText): array
    {
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
        ];
    }
}
