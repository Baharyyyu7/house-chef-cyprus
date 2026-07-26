<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Cache\CacheKeyFactory;
use App\Services\NutritionService;
use Illuminate\Console\Command;

final class CacheWarm extends Command
{
    protected $signature = 'cache:warm {--section=all : recipes, nutrition, lookups veya all}';

    protected $description = 'Güvenli temel uygulama cache alanlarını ısıtır';

    public function handle(NutritionService $nutrition, CacheKeyFactory $keys): int
    {
        $section = (string) $this->option('section');
        if (! in_array($section, ['all', 'recipes', 'nutrition', 'lookups'], true)) {
            $this->error('Geçersiz bölüm. recipes, nutrition, lookups veya all kullanın.');

            return self::FAILURE;
        }
        if (in_array($section, ['all', 'nutrition', 'recipes'], true)) {
            Recipe::query()->where('status', 'published')->cursor()->each(fn (Recipe $recipe) => $nutrition->calculate($recipe));
        }
        if (in_array($section, ['all', 'lookups'], true)) {
            cache()->remember('lookups:cuisines:v1', now()->addHour(), fn () => Cuisine::query()->orderBy('name')->get(['public_id', 'name', 'slug'])->all());
            cache()->remember('lookups:categories:v1', now()->addHour(), fn () => Category::query()->orderBy('name')->get(['public_id', 'name', 'slug'])->all());
        }
        cache()->forever($keys->warmupMarker($section), now()->toIso8601String());
        $this->info('Cache warmup tamamlandı: '.$section);

        return self::SUCCESS;
    }
}
