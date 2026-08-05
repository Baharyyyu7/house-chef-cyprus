<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ApprovedMediaImportCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $sourcePath;

    private string $manifestPath;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $directory = storage_path('app/media-import/recipes');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $this->sourcePath = $directory.'/recipe--onayli-test-tarifi.png';
        $image = imagecreatetruecolor(1600, 1350);
        imagefill($image, 0, 0, imagecolorallocate($image, 210, 120, 50));
        imagepng($image, $this->sourcePath);
        imagedestroy($image);
        $this->manifestPath = storage_path('app/media-import/approved-media-test-manifest.json');
    }

    protected function tearDown(): void
    {
        @unlink($this->sourcePath);
        @unlink($this->manifestPath);
        parent::tearDown();
    }

    public function test_default_command_mode_is_a_non_mutating_dry_run(): void
    {
        $recipe = Recipe::query()->create(['title' => 'Onaylı Test Tarifi', 'slug' => 'onayli-test-tarifi', 'status' => 'published', 'verification_status' => 'verified']);
        $this->writeManifest($recipe);

        $this->artisan('media:import-approved', ['--manifest' => $this->manifestPath, '--allow-controlled-resample' => true, '--json' => true])
            ->assertExitCode(0);

        $this->assertSame(0, Media::query()->count());
    }

    public function test_strict_import_supersedes_fallback_only_after_variants_are_ready_and_is_idempotent(): void
    {
        $recipe = Recipe::query()->create(['title' => 'Onaylı Test Tarifi', 'slug' => 'onayli-test-tarifi', 'status' => 'published', 'verification_status' => 'verified']);
        $this->writeManifest($recipe);
        $fallback = $recipe->media()->create(['kind' => 'fallback', 'disk' => 'public', 'path' => 'media/fallback.png', 'mime_type' => 'image/png', 'size' => 1, 'visibility' => 'public', 'media_status' => 'branded_fallback', 'is_placeholder' => true]);

        $this->artisan('media:import-approved', ['--manifest' => $this->manifestPath, '--strict' => true, '--replace-fallbacks' => true, '--allow-controlled-resample' => true, '--json' => true])
            ->assertExitCode(0);

        $this->assertSame('superseded', $fallback->fresh()->media_status);
        $media = $recipe->media()->where('media_status', 'approved_real_media')->firstOrFail();
        $this->assertTrue($media->generated_by_ai);
        $this->assertSame('SYNTHETIC_APPROVED', $media->ai_review_status);
        $this->assertArrayHasKey('hero', (array) $media->responsive_variants);
        $this->assertArrayHasKey('portrait', (array) $media->responsive_variants);

        $this->artisan('media:import-approved', ['--manifest' => $this->manifestPath, '--strict' => true, '--replace-fallbacks' => true, '--allow-controlled-resample' => true, '--json' => true])
            ->assertExitCode(0);
        $this->assertSame(2, $recipe->media()->count());
    }

    /** @return array<string,mixed> */
    private function writeManifest(Recipe $recipe): void
    {
        file_put_contents($this->manifestPath, json_encode(['entries' => [[
            'content_type' => 'recipe', 'public_id' => $recipe->public_id, 'source_filename' => 'recipe--onayli-test-tarifi.png',
            'identity_match' => true, 'approval_state' => 'APPROVED', 'reviewer' => 'House Chef Cyprus Editorial',
            'provenance' => ['provider' => 'built_in_image_generation', 'model' => 'built_in_image_generation', 'generated_at' => '2026-08-04T00:00:00+03:00', 'prompt_version' => 'test'],
        ]]], JSON_THROW_ON_ERROR));
    }
}
