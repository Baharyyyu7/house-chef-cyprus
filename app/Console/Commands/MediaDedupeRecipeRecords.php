<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Removes repeated media rows that point the same file at the same recipe.
 * Only database rows go; the file stays because the kept row still uses it.
 * Dry-run by default.
 */
final class MediaDedupeRecipeRecords extends Command
{
    protected $signature = 'media:dedupe-recipe-records {--apply : Fazla kayıtları sil} {--json}';

    protected $description = 'Aynı tarife aynı görselin birden çok kez bağlandığı mükerrer medya kayıtlarını temizler.';

    public function handle(): int
    {
        $groups = DB::table('media')
            ->where('media_status', 'approved_real_media')
            ->where('mediable_type', Recipe::class)
            ->whereNotNull('path')
            ->select('path', 'mediable_id')
            ->groupBy('path', 'mediable_id')
            ->havingRaw('count(*) > 1')
            ->get();

        $removed = 0;
        foreach ($groups as $group) {
            $ids = DB::table('media')
                ->where('media_status', 'approved_real_media')
                ->where('mediable_type', Recipe::class)
                ->where('mediable_id', $group->mediable_id)
                ->where('path', $group->path)
                ->orderBy('sort_order')->orderBy('id')
                ->pluck('id');
            $keep = $ids->shift();
            $removed += $ids->count();

            if ($this->option('apply') && $ids->isNotEmpty()) {
                DB::transaction(function () use ($ids, $keep): void {
                    DB::table('recipe_media_candidates')->whereIn('media_id', $ids)->update(['media_id' => $keep]);
                    DB::table('ai_image_generations')->whereIn('media_id', $ids)->update(['media_id' => $keep]);
                    DB::table('blog_posts')->whereIn('hero_media_id', $ids)->update(['hero_media_id' => $keep]);
                    DB::table('media')->whereIn('id', $ids)->delete();
                });
            }
        }

        $result = ['groups' => $groups->count(), 'extra_records' => $removed, 'applied' => (bool) $this->option('apply')];
        $this->option('json')
            ? $this->line((string) json_encode($result))
            : $this->info(($result['applied'] ? 'Silindi' : 'Dry-run').': '.$removed.' fazla kayıt, '.$groups->count().' görsel.');

        return self::SUCCESS;
    }
}
