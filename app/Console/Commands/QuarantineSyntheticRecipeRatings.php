<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class QuarantineSyntheticRecipeRatings extends Command
{
    protected $signature = 'recipe-ratings:quarantine-synthetic {--apply : Archive and remove exact synthetic matches on the production server}';

    protected $description = 'Report known synthetic recipe votes; --apply privately archives verified rows before removal.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if ($apply && ! app()->environment('production', 'testing')) {
            $this->error('Apply is restricted to the production server. Run without --apply for a read-only report.');

            return self::FAILURE;
        }

        if ($apply && ! app()->environment('testing') && (
            config('filesystems.disks.local.driver') !== 'local'
            || rtrim((string) config('filesystems.disks.local.root'), '/') !== storage_path('app/private')
        )) {
            $this->error('The local disk must use storage/app/private. No rows were deleted.');

            return self::FAILURE;
        }

        $knownHashes = [];
        $candidates = DB::table('recipe_ratings')->orderBy('id')->get()->filter(function ($row) use (&$knownHashes): bool {
            if (! isset($knownHashes[$row->recipe_id])) {
                $knownHashes[$row->recipe_id] = [];
                for ($i = 1; $i <= 36; $i++) {
                    $knownHashes[$row->recipe_id][hash('sha256', "test-kitchen-ip-{$row->recipe_id}-{$i}")] = true;
                }
            }

            return isset($knownHashes[$row->recipe_id][$row->ip_hash]);
        })->keyBy('id');

        $this->info('Matched: '.$candidates->count().' | Recipes: '.$candidates->pluck('recipe_id')->unique()->count());

        if (! $apply || $candidates->isEmpty()) {
            $this->line($apply ? 'No rows to quarantine.' : 'Dry run: no archive written and no rows deleted.');

            return self::SUCCESS;
        }

        try {
            [$deleted, $archive, $checksum] = DB::transaction(function () use ($candidates): array {
                $rows = collect();
                foreach ($candidates->keys()->chunk(1000) as $ids) {
                    $locked = DB::table('recipe_ratings')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
                    foreach ($locked as $row) {
                        if ((array) $row === (array) $candidates->get($row->id)) {
                            $rows->push($row);
                        }
                    }
                }

                if ($rows->isEmpty()) {
                    return [0, null, null];
                }

                $disk = Storage::disk('local');
                $archive = 'quarantine/recipe-ratings-'.Str::uuid().'.jsonl';
                $bytes = $rows->map(fn ($row) => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))->implode("\n")."\n";
                $manifest = [
                    'source_commit' => '79c36cf',
                    'archive' => $archive,
                    'row_count' => $rows->count(),
                    'sha256' => hash('sha256', $bytes),
                    'created_at' => now()->toIso8601String(),
                ];

                if (! $disk->put($archive, $bytes, ['visibility' => 'private'])) {
                    throw new RuntimeException('Archive write failed.');
                }

                $readback = $disk->get($archive);
                if (! is_string($readback)
                    || substr_count($readback, "\n") !== $manifest['row_count']
                    || ! hash_equals($manifest['sha256'], hash('sha256', $readback))) {
                    throw new RuntimeException('Archive verification failed.');
                }

                $manifestPath = $archive.'.manifest.json';
                $manifestBytes = json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
                if (! $disk->put($manifestPath, $manifestBytes, ['visibility' => 'private']) || $disk->get($manifestPath) !== $manifestBytes) {
                    throw new RuntimeException('Manifest verification failed.');
                }

                $deleted = 0;
                foreach ($rows->pluck('id')->chunk(1000) as $ids) {
                    $deleted += DB::table('recipe_ratings')->whereIn('id', $ids)->delete();
                }
                if ($deleted !== $rows->count()) {
                    throw new RuntimeException('Deletion count changed.');
                }

                return [$deleted, $disk->path($archive), $manifest['sha256']];
            });
        } catch (Throwable) {
            $this->error('Quarantine aborted; no rows were deleted. Check the private archive and database availability.');

            return self::FAILURE;
        }

        $this->info('Quarantined: '.$deleted.' | Changed or removed since discovery: '.($candidates->count() - $deleted));
        if ($archive !== null) {
            $this->line('Private archive: '.$archive);
            $this->line('Private manifest: '.$archive.'.manifest.json');
            $this->line('Archive SHA256: '.$checksum);
        }

        return self::SUCCESS;
    }
}
