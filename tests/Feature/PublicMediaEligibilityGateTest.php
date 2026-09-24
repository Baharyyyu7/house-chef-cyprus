<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PublicMediaEligibilityGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_complete_unique_human_reviewed_media_can_reach_a_public_recipe_page(): void
    {
        Storage::fake('public');
        $recipe = $this->recipe('Tarife özel görsel');
        $media = $this->completeMedia($recipe, 'media/recipe/unique.jpg', 'unique-checksum');

        $this->assertTrue(app(PublicMediaEligibilityService::class)->isEligible($media));
        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('/storage/media/recipe/unique.jpg');
    }

    public function test_a_missing_variant_or_source_is_never_presented_as_a_recipe_photo(): void
    {
        Storage::fake('public');
        $recipe = $this->recipe('Eksik varyant görseli');
        $media = $this->completeMedia($recipe, 'media/recipe/incomplete.jpg', 'incomplete-checksum');
        $media->update(['responsive_variants' => []]);

        $this->assertSame('responsive_variants_incomplete', app(PublicMediaEligibilityService::class)->reason($media->fresh()));
        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('Onaylı yemek fotoğrafı henüz yok')
            ->assertDontSee('/storage/media/recipe/incomplete.jpg');
    }

    public function test_reused_media_is_rejected_for_every_public_consumer(): void
    {
        Storage::fake('public');
        $first = $this->recipe('İlk tarif');
        $second = $this->recipe('İkinci tarif');
        $firstMedia = $this->completeMedia($first, 'media/recipe/reused.jpg', 'reused-checksum');
        $this->completeMedia($second, 'media/recipe/other.jpg', 'reused-checksum');

        $this->assertSame('duplicate_media_usage', app(PublicMediaEligibilityService::class)->reason($firstMedia->fresh()));
        $this->get(route('recipes.show', $first))
            ->assertOk()
            ->assertSee('Onaylı yemek fotoğrafı henüz yok')
            ->assertDontSee('/storage/media/recipe/reused.jpg');
    }

    public function test_seed_or_system_accounts_can_never_make_media_publicly_eligible(): void
    {
        Storage::fake('public');
        $recipe = $this->recipe('Sistem incelemesi');
        $system = User::factory()->create(['email' => 'seed@housechef.local', 'role' => 'admin', 'email_verified_at' => now()]);
        $media = $this->completeMedia($recipe, 'media/recipe/system.jpg', 'system-checksum', $system);

        $this->assertSame('human_approval_missing', app(PublicMediaEligibilityService::class)->reason($media));
    }

    public function test_a_blog_with_an_unreviewed_or_incomplete_hero_never_exposes_it_in_html_or_schema(): void
    {
        Storage::fake('public');
        $post = BlogPost::query()->create([
            'title' => 'Nohut saklama rehberi',
            'slug' => 'nohut-saklama-rehberi',
            'excerpt' => 'Nohutla ilgili özgün editoryal notlar.',
            'body' => '## Nohut\n\nBu yazı editoryal inceleme içindir.',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $path = 'media/blog/unreviewed.jpg';
        Storage::disk('public')->put($path, 'source');
        $media = $post->media()->create([
            'kind' => 'image',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 12,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 95,
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'verification_status' => 'verified',
            'media_reviewer_user_id' => $reviewer->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $reviewer->id,
            'approved_at' => now(),
            'checksum_sha256' => 'blog-unreviewed-checksum',
        ]);
        $post->update(['hero_media_id' => $media->id]);

        $this->assertSame('responsive_variants_incomplete', app(PublicMediaEligibilityService::class)->reason($media));
        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertDontSee('/storage/media/blog/unreviewed.jpg');
    }

    private function recipe(string $title): Recipe
    {
        return Recipe::query()->create([
            'title' => $title,
            'slug' => str($title)->slug().'-'.str()->lower(str()->random(6)),
            'excerpt' => 'Tarife özel görsel doğrulama testi.',
            'description' => 'Tarife özel görsel doğrulama testi.',
            'servings' => 2,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'difficulty' => 'easy',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    private function completeMedia(Recipe $recipe, string $path, string $checksum, ?User $reviewer = null): Media
    {
        $reviewer ??= User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        Storage::disk('public')->put($path, 'source');
        $variants = [];
        foreach (['thumbnail', 'square', 'card', 'open_graph', 'hero'] as $profile) {
            $jpeg = "media/recipe/{$checksum}-{$profile}.jpg";
            $webp = "media/recipe/{$checksum}-{$profile}.webp";
            Storage::disk('public')->put($jpeg, 'jpeg');
            Storage::disk('public')->put($webp, 'webp');
            $variants[] = ['name' => $profile, 'width' => 1200, 'height' => 800, 'jpeg' => $jpeg, 'webp' => $webp];
        }

        return $recipe->media()->create([
            'kind' => 'image',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 12,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 95,
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'optimization_status' => 'ready',
            'responsive_variants' => $variants,
            'verification_status' => 'verified',
            'alt_text' => $recipe->title.' için doğrulanmış görsel',
            'source_name' => 'Editorial test',
            'license' => 'Test license',
            'media_reviewer_user_id' => $reviewer->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $reviewer->id,
            'approved_at' => now(),
            'checksum_sha256' => $checksum,
        ]);
    }
}
