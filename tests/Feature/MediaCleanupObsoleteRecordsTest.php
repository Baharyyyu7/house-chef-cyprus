<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MediaCleanupObsoleteRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_but_does_not_delete_obsolete_records(): void
    {
        $recipe = $this->recipe();
        $recipe->media()->create($this->media(['path' => 'fallbacks/recipes/old.svg', 'is_placeholder' => true]));
        $recipe->media()->create($this->media(['path' => 'media/real-unpublished.png']));

        $this->artisan('media:cleanup-obsolete-records --json')
            ->expectsOutputToContain('"candidates":1')
            ->assertSuccessful();

        $this->assertSame(2, Media::query()->count());
    }

    public function test_apply_requires_backup_and_preserves_linked_real_media(): void
    {
        $recipe = $this->recipe();
        $placeholder = $recipe->media()->create($this->media(['path' => 'placeholders/recipe.svg', 'is_placeholder' => true]));
        $real = $recipe->media()->create($this->media(['path' => 'media/real-unpublished.png']));

        $this->artisan('media:cleanup-obsolete-records --apply')->assertFailed();

        $manifest = tempnam(sys_get_temp_dir(), 'hc-backup-');
        file_put_contents($manifest, '{"status":"verified"}');

        $this->artisan('media:cleanup-obsolete-records', ['--apply' => true, '--backup' => $manifest])
            ->assertSuccessful();

        $this->assertDatabaseMissing('media', ['id' => $placeholder->id]);
        $this->assertDatabaseHas('media', ['id' => $real->id]);
        @unlink($manifest);
    }

    /** @param array<string,mixed> $overrides */
    private function media(array $overrides = []): array
    {
        return array_merge([
            'kind' => 'image',
            'disk' => 'public',
            'path' => 'media/item.png',
            'mime_type' => 'image/png',
            'visibility' => 'public',
            'media_status' => 'candidate_media',
            'verification_status' => 'researched',
            'is_placeholder' => false,
        ], $overrides);
    }

    private function recipe(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Taslak Tarif',
            'slug' => 'taslak-'.uniqid(),
            'status' => 'draft',
            'verification_status' => 'verified',
        ]);
    }
}
