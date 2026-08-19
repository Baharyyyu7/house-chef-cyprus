<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApprovalQueueShowsOnlyRealPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_fallback_covers_never_reach_the_approval_queue(): void
    {
        // Kuyruğu 3212 tipografik kapak dolduruyordu: bunlar sistemin fotoğraf
        // olmadığında kendi çizdiği yer tutucular, onay bekleyen çekimler
        // değil. Gerçek yemek fotoğrafları arada kayboluyordu.
        $yazi = BlogPost::query()->create([
            'title' => 'Yer Tutucu Kapaklı Yazı',
            'slug' => 'yer-tutucu-'.uniqid(),
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $yazi->media()->create([
            'kind' => 'image',
            'disk' => 'public',
            'path' => 'fallbacks/blog/'.uniqid().'.svg',
            'mime_type' => 'image/svg+xml',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
        ]);

        $this->actingAs($this->yonetici())
            ->get(route('admin.media.index', ['durum' => 'onay']))
            ->assertOk()
            ->assertDontSee('Yer Tutucu Kapaklı Yazı');
    }

    public function test_a_delivered_recipe_photo_does_reach_the_queue(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Teslim Edilmiş Fotoğraflı Tarif',
            'slug' => 'teslim-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/ABC/source.png',
            'mime_type' => 'image/png',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
        ]);

        $this->actingAs($this->yonetici())
            ->get(route('admin.media.index', ['durum' => 'onay']))
            ->assertOk()
            ->assertSee('Teslim Edilmiş Fotoğraflı Tarif');
    }

    private function yonetici(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
