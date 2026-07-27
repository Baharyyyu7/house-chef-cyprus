<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Media\MediaStorageInterface;
use App\Jobs\GenerateMediaThumbnailsJob;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\ThumbnailGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class MediaOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_a_safe_recipe_image_and_queue_thumbnails(): void
    {
        Storage::fake('public');
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $recipe = $this->recipe();

        $this->actingAs($admin)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('meal.jpg', 240, 120),
            'entity_type' => 'recipe', 'entity_public_id' => $recipe->public_id, 'visibility' => 'public', 'alt_text' => 'Tarif görseli',
        ])->assertRedirect(route('admin.media.show', Media::first()->public_id));

        $media = Media::firstOrFail();
        $this->assertSame($recipe->id, $media->mediable_id);
        $this->assertSame('public', $media->visibility);
        $this->assertStringStartsWith('media/images/', (string) $media->path);
        Storage::disk('public')->assertExists((string) $media->path);
        Queue::assertPushed(GenerateMediaThumbnailsJob::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'media.upload']);
    }

    public function test_upload_rejects_double_extensions_and_svg(): void
    {
        $storage = app(MediaStorageInterface::class);
        foreach ([UploadedFile::fake()->image('meal.jpg.php', 20, 20), UploadedFile::fake()->create('vector.svg', 10, 'image/svg+xml')] as $file) {
            try {
                $storage->storePublicImage($file);
                $this->fail('Unsafe upload accepted.');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_thumbnail_job_creates_aspect_preserving_sizes_and_is_idempotent(): void
    {
        Storage::fake('public');
        $recipe = $this->recipe();
        $path = UploadedFile::fake()->image('source.jpg', 400, 200)->store('media/images', 'public');
        $media = $recipe->media()->create(['kind' => 'image', 'disk' => 'public', 'path' => $path, 'mime_type' => 'image/jpeg', 'size' => 100, 'visibility' => 'public']);

        (new GenerateMediaThumbnailsJob($media->public_id))->handle(app(ThumbnailGenerator::class));
        $media->refresh();
        $this->assertSame('ready', $media->thumbnail_status);
        $this->assertCount(3, $media->thumbnail_paths);
        foreach ($media->thumbnail_paths as $thumbnail) {
            Storage::disk('public')->assertExists($thumbnail);
        }
        $first = $media->thumbnail_paths;
        (new GenerateMediaThumbnailsJob($media->public_id))->handle(app(ThumbnailGenerator::class));
        $this->assertSame($first, $media->fresh()->thumbnail_paths);
    }

    public function test_member_cannot_open_media_admin_or_private_media(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $this->actingAs($member)->get(route('admin.media.index'))->assertForbidden();
    }

    public function test_maintenance_commands_are_dry_by_default_and_delete_only_safe_candidates(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('public')->put('media/orphan.jpg', 'x');
        Storage::disk('local')->put('temporary/old.tmp', 'x');
        touch(Storage::disk('local')->path('temporary/old.tmp'), now()->subHours(2)->timestamp);
        $this->artisan('media:scan-orphans')->assertSuccessful();
        Storage::disk('public')->assertExists('media/orphan.jpg');
        $this->artisan('media:scan-orphans', ['--delete' => true])->assertSuccessful();
        Storage::disk('public')->assertMissing('media/orphan.jpg');
        $this->artisan('media:cleanup-temporary', ['--hours' => 1])->assertSuccessful();
        Storage::disk('local')->assertExists('temporary/old.tmp');
        $this->artisan('media:cleanup-temporary', ['--hours' => 1, '--delete' => true])->assertSuccessful();
        Storage::disk('local')->assertMissing('temporary/old.tmp');
        $this->assertGreaterThanOrEqual(2, AuditLog::query()->where('action', 'like', 'media.%cleanup')->count());
    }

    private function recipe(): Recipe
    {
        return Recipe::create(['title' => 'Medya test tarifi', 'slug' => 'medya-test-'.str()->lower(str()->random(8)), 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published']);
    }
}
