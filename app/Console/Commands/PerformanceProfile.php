<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class PerformanceProfile extends Command
{
    protected $signature = 'performance:profile {--json : Makine okunabilir çıktı}';

    protected $description = 'Temel tarif sorgularının yerel performans baseline değerlerini gösterir';

    public function handle(RecipeSearchInterface $search): int
    {
        $metrics = [];
        $this->measure($metrics, 'recipe_listing', fn () => Recipe::query()->where('status', 'published')->with(['cuisine:id,name,slug', 'category:id,name,slug'])->limit((int) config('performance.profile_limit'))->get());
        $this->measure($metrics, 'recipe_search', fn () => $search->search(['per_page' => min(12, (int) config('performance.pagination.api_max'))]));

        if ($this->option('json')) {
            $this->line((string) json_encode(['metrics' => $metrics], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Akış', 'Sorgu', 'Süre (ms)'], array_map(fn (array $metric) => [$metric['name'], $metric['queries'], $metric['duration_ms']], $metrics));
        }

        return self::SUCCESS;
    }

    /** @param list<array{name:string,queries:int,duration_ms:int}> $metrics @param callable():mixed $callback */
    private function measure(array &$metrics, string $name, callable $callback): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $started = hrtime(true);
        $callback();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $metrics[] = ['name' => $name, 'queries' => $queries, 'duration_ms' => (int) round((hrtime(true) - $started) / 1_000_000)];
    }
}
