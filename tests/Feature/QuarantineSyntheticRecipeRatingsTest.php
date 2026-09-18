<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

final class QuarantineSyntheticRecipeRatingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->deleteDirectory('quarantine');

        parent::tearDown();
    }

    public function test_default_run_reports_matches_without_writing_or_deleting(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));

        $this->assertSame(0, Artisan::call('recipe-ratings:quarantine-synthetic'));
        $this->assertStringContainsString('Matched: 1', Artisan::output());
        $this->assertDatabaseHas('recipe_ratings', ['id' => $id]);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_apply_archives_complete_exact_matches_and_preserves_other_votes(): void
    {
        $recipe = $this->recipe();
        $first = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $last = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-36"));
        $real = $this->rating($recipe, hash('sha256', 'real-visitor'));
        $outside = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-37"));
        $wrongRecipe = $this->rating($recipe, hash('sha256', 'test-kitchen-ip-999999-1'));
        $expectedRows = DB::table('recipe_ratings')->whereIn('id', [$first, $last])->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();

        $this->assertSame(0, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
        $output = Artisan::output();
        $disk = Storage::disk('local');
        $files = $disk->allFiles('quarantine');
        $this->assertCount(2, $files);
        $archive = collect($files)->first(fn ($path) => str_ends_with($path, '.jsonl'));
        $manifestPath = collect($files)->first(fn ($path) => str_ends_with($path, '.json'));
        $bytes = $disk->get($archive);
        $rows = array_map(fn ($line) => json_decode($line, true, 512, JSON_THROW_ON_ERROR), explode("\n", rtrim($bytes, "\n")));
        $manifest = json_decode($disk->get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($expectedRows, $rows);
        $this->assertSame(2, $manifest['row_count']);
        $this->assertSame(hash('sha256', $bytes), $manifest['sha256']);
        $this->assertSame($archive, $manifest['archive']);
        $this->assertSame('private', $disk->getVisibility($archive));
        $this->assertStringContainsString($disk->path($archive), $output);
        $this->assertStringContainsString($disk->path($manifestPath), $output);
        $this->assertStringContainsString($manifest['sha256'], $output);
        $this->assertStringNotContainsString($expectedRows[0]['ip_hash'], $output);
        $this->assertStringNotContainsString($expectedRows[0]['voter_key'], $output);
        $this->assertSame([$real, $outside, $wrongRecipe], DB::table('recipe_ratings')->orderBy('id')->pluck('id')->all());
    }

    public function test_rerun_keeps_the_archive_and_does_not_create_an_empty_archive(): void
    {
        $recipe = $this->recipe();
        $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));

        $this->assertSame(0, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
        $files = Storage::disk('local')->allFiles('quarantine');
        $this->assertSame(0, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));

        $this->assertStringContainsString('Matched: 0', Artisan::output());
        $this->assertSame($files, Storage::disk('local')->allFiles('quarantine'));
        $this->assertDatabaseCount('recipe_ratings', 0);
    }

    public function test_failed_archive_write_never_deletes_votes(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $disk = Storage::disk('local');
        $failedDisk = Mockery::mock(FilesystemAdapter::class);
        $failedDisk->shouldReceive('put')->andReturnFalse();
        Storage::set('local', $failedDisk);

        try {
            $this->assertSame(1, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
            $this->assertDatabaseHas('recipe_ratings', ['id' => $id]);
        } finally {
            Storage::set('local', $disk);
        }
    }

    public function test_corrupted_archive_readback_never_deletes_votes(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $disk = Storage::disk('local');
        $corruptedDisk = Mockery::mock($disk)->makePartial();
        $corruptedDisk->shouldReceive('get')->andReturn("{}\n");
        Storage::set('local', $corruptedDisk);

        try {
            $this->assertSame(1, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
            $this->assertDatabaseHas('recipe_ratings', ['id' => $id]);
        } finally {
            Storage::set('local', $disk);
        }
    }

    public function test_a_vote_changed_after_discovery_is_preserved(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $edited = false;
        DB::listen(function (QueryExecuted $query) use ($id, &$edited): void {
            if (! $edited && str_starts_with($query->sql, 'select') && str_contains($query->sql, 'recipe_ratings')) {
                $edited = true;
                DB::table('recipe_ratings')->where('id', $id)->update(['rating' => 2]);
            }
        });

        $this->assertSame(0, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));

        $this->assertTrue($edited);
        $this->assertDatabaseHas('recipe_ratings', ['id' => $id, 'rating' => 2]);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_failed_manifest_write_preserves_votes_even_with_a_complete_archive(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $disk = Storage::disk('local');
        $failedDisk = Mockery::mock($disk)->makePartial();
        $failedDisk->shouldReceive('put')->withArgs(fn ($path, $bytes, $options) => str_ends_with($path, '.manifest.json'))->andReturnFalse();
        Storage::set('local', $failedDisk);

        try {
            $this->assertSame(1, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
            $this->assertDatabaseHas('recipe_ratings', ['id' => $id]);
            $this->assertCount(1, $disk->allFiles('quarantine'));
        } finally {
            Storage::set('local', $disk);
        }
    }

    public function test_apply_is_refused_in_the_local_environment(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $this->app['env'] = 'local';

        $this->assertSame(1, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
        $this->assertDatabaseHas('recipe_ratings', ['id' => $id]);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_production_apply_refuses_a_public_archive_root(): void
    {
        $recipe = $this->recipe();
        $id = $this->rating($recipe, hash('sha256', "test-kitchen-ip-{$recipe->id}-1"));
        $this->app['env'] = 'production';
        config(['filesystems.disks.local.root' => storage_path('app/public')]);

        $this->assertSame(1, Artisan::call('recipe-ratings:quarantine-synthetic', ['--apply' => true]));
        $this->assertDatabaseHas('recipe_ratings', ['id' => $id]);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    private function recipe(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Karantina test tarifi',
            'slug' => 'karantina-'.Str::uuid(),
            'status' => 'published',
        ]);
    }

    private function rating(Recipe $recipe, string $ipHash): int
    {
        return DB::table('recipe_ratings')->insertGetId([
            'recipe_id' => $recipe->id,
            'rating' => 5,
            'voter_key' => (string) Str::uuid(),
            'ip_hash' => $ipHash,
            'created_at' => '2026-09-01 12:34:56',
        ]);
    }
}
