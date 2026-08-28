<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateMediaThumbnailsJob;
use App\Models\Media;
use App\Models\Recipe;
use App\Services\Media\ThumbnailGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ThumbnailJobSkipsPlaceholdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_fallback_placeholder_does_not_fail_the_job(): void
    {
        // Yer tutucular SVG; üretici izin verilen MIME listesinde
        // olmadıkları için MEDIA_SOURCE_UNAVAILABLE atıyordu. İş üç kez
        // denenip kalıcı olarak düşüyor ve kuyrukta ölü iş bırakıyordu.
        $media = $this->media('fallbacks/blog/ABC.svg', 'image/svg+xml');

        (new GenerateMediaThumbnailsJob($media->public_id))->handle(app(ThumbnailGenerator::class));

        $this->assertNotSame('failed', $media->fresh()->thumbnail_status);
    }

    public function test_media_flagged_as_placeholder_is_skipped_too(): void
    {
        // Yol düzeni değişirse bayrak yakalamalı.
        $media = $this->media('media/approved-synthetic/recipe/ABC/source.png', 'image/png', true);

        (new GenerateMediaThumbnailsJob($media->public_id))->handle(app(ThumbnailGenerator::class));

        $this->assertNotSame('failed', $media->fresh()->thumbnail_status);
    }

    public function test_a_missing_media_record_is_still_a_no_op(): void
    {
        (new GenerateMediaThumbnailsJob('01OLMAYANMEDYA0000000000'))->handle(app(ThumbnailGenerator::class));

        $this->addToAssertionCount(1);
    }

    private function media(string $path, string $mime, bool $placeholder = false): Media
    {
        Storage::disk('public')->put($path, 'x');

        $tarif = Recipe::query()->create([
            'title' => 'Yer Tutuculu Tarif',
            'slug' => 'yer-tutuculu-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        return $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'visibility' => 'public',
            'is_placeholder' => $placeholder,
        ]);
    }
}
