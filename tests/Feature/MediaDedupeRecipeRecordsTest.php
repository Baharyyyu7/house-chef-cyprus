<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MediaDedupeRecipeRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_and_apply_keeps_one_row_per_recipe_and_file(): void
    {
        $recipe = Recipe::query()->create(['title' => 'Menemen', 'slug' => 'menemen', 'status' => 'published', 'verification_status' => 'verified']);
        $other = Recipe::query()->create(['title' => 'Cacik', 'slug' => 'cacik', 'status' => 'published', 'verification_status' => 'verified']);
        $keep = $this->media($recipe, 'media/a.jpg', 0);
        $this->media($recipe, 'media/a.jpg', 1);
        $this->media($recipe, 'media/a.jpg', 2);
        $this->media($other, 'media/b.jpg', 0);

        $this->artisan('media:dedupe-recipe-records --json')->expectsOutputToContain('"extra_records":2')->assertSuccessful();
        $this->assertSame(4, Media::query()->count());

        $this->artisan('media:dedupe-recipe-records --apply --json')->expectsOutputToContain('"applied":true')->assertSuccessful();
        $this->assertSame([$keep->id], $recipe->media()->pluck('id')->all());
        $this->assertSame(1, $other->media()->count());
    }

    private function media(Recipe $recipe, string $path, int $sort): Media
    {
        return $recipe->media()->create([
            'public_id' => (string) Str::ulid(), 'kind' => 'image', 'disk' => 'public', 'path' => $path, 'visibility' => 'public',
            'media_status' => 'approved_real_media', 'verification_status' => 'verified', 'license_review_status' => 'approved',
            'attribution_review_status' => 'approved', 'is_placeholder' => false, 'sort_order' => $sort,
        ]);
    }
}
