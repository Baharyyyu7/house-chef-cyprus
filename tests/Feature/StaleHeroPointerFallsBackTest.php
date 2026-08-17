<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\User;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * hero_media_id sabit bir kayda işaret eder ve o kayıt yayına uygunluğunu her
 * an yitirebilir. "Kıbrıs Mutfağına Başlangıç Rehberi" yazısının dört medyası
 * vardı; biri onaylı ve kusursuzdu, ama hero düşmüş olanı gösterdiği için
 * yazıda hiç fotoğraf çıkmıyordu.
 */
final class StaleHeroPointerFallsBackTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_stale_hero_pointer_falls_back_to_an_eligible_image(): void
    {
        [$post, $uygun] = $this->postWithStaleHero();

        $secilen = app(PublicMediaEligibilityService::class)
            ->heroOrFirstEligible($post->heroMedia, $post->media);

        $this->assertNotNull($secilen);
        $this->assertSame($uygun->id, $secilen->id);
    }

    public function test_a_valid_hero_pointer_is_respected(): void
    {
        [$post, $uygun] = $this->postWithStaleHero();
        $post->forceFill(['hero_media_id' => $uygun->id])->save();

        $secilen = app(PublicMediaEligibilityService::class)
            ->heroOrFirstEligible($post->fresh()->heroMedia, $post->media);

        $this->assertSame($uygun->id, $secilen->id);
    }

    public function test_nothing_is_shown_when_no_image_qualifies(): void
    {
        [$post] = $this->postWithStaleHero(uygunEkle: false);

        $this->assertNull(app(PublicMediaEligibilityService::class)
            ->heroOrFirstEligible($post->heroMedia, $post->media));
    }

    public function test_the_listing_shows_the_recovered_image(): void
    {
        [$post] = $this->postWithStaleHero();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('media/blog/uygun', false);
    }

    /** @return array{0: BlogPost, 1: ?Media} */
    private function postWithStaleHero(bool $uygunEkle = true): array
    {
        Storage::fake('public');

        $post = BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Kıbrıs Mutfağına Başlangıç Rehberi',
            'slug' => 'kibris-baslangic',
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // Hero: onayı geri alınmış, artık yayına uygun olmayan kayıt.
        $bayat = $post->media()->create([
            'public_id' => (string) Str::ulid(),
            'kind' => 'image',
            'disk' => 'public',
            'path' => 'media/blog/bayat/source.jpg',
            'visibility' => 'public',
            'media_status' => 'no_media',
        ]);

        $post->forceFill(['hero_media_id' => $bayat->id])->save();

        $uygun = $uygunEkle ? $this->eligibleMedia($post) : null;

        return [$post->fresh(['heroMedia', 'media']), $uygun];
    }

    private function eligibleMedia(BlogPost $post): Media
    {
        $kaynak = 'media/blog/uygun/source.jpg';
        Storage::disk('public')->put($kaynak, 'binary');

        $variants = [];
        foreach (array_keys((array) config('media_excellence.blog_profiles')) as $profile) {
            $variants[$profile] = ['name' => $profile];
            foreach (['jpeg' => 'jpg', 'webp' => 'webp'] as $format => $uzanti) {
                $yol = "media/blog/uygun/{$profile}.{$uzanti}";
                Storage::disk('public')->put($yol, 'binary');
                $variants[$profile][$format] = $yol;
            }
        }

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $media = $post->media()->create([
            'public_id' => (string) Str::ulid(),
            'kind' => 'blog_synthetic',
            'disk' => 'public',
            'path' => $kaynak,
            'width' => 1672,
            'height' => 941,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 100,
            'license' => 'House Chef Cyprus',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'verification_status' => 'verified',
            'processing_status' => 'ready',
        ]);

        $media->forceFill([
            'responsive_variants' => $variants,
            'media_reviewer_user_id' => $admin->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $admin->id,
            'approved_at' => now(),
        ])->save();

        return $media;
    }
}
