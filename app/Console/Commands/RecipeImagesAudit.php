<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

final class RecipeImagesAudit extends Command
{
    protected $signature = 'recipe-images:audit {--json : Makine okunabilir çıktı} {--missing-only : Yalnız eksik görseli olan tarifler} {--placeholder-only : Yalnız yer tutucu görseli olan tarifler}';

    protected $description = 'Tarif görsel gerçekliği ve üretim önceliklerini raporlar';

    public function handle(): int
    {
        $recipes = Recipe::query()->with(['media', 'cuisine', 'category'])->where('status', 'published')->orderBy('is_featured', 'desc')->orderBy('title')->get();
        $rows = $recipes->map(function (Recipe $recipe): array {
            $media = $recipe->media;
            $placeholder = $media->isNotEmpty() && $media->every(fn ($item): bool => (bool) $item->is_placeholder);
            $missing = $media->isEmpty();

            return ['public_id' => (string) $recipe->public_id, 'title' => $recipe->title, 'cuisine' => $recipe->cuisine?->name, 'category' => $recipe->category?->name, 'featured' => (bool) $recipe->is_featured, 'missing' => $missing, 'placeholder' => $placeholder, 'verified' => $media->contains(fn ($item): bool => ! $item->is_placeholder && $item->verification_status === 'verified'), 'alt_complete' => $media->isNotEmpty() && $media->every(fn ($item): bool => filled($item->alt_text)), 'thumbnail_complete' => $media->isNotEmpty() && $media->every(fn ($item): bool => $item->thumbnail_status === 'ready'), 'license_complete' => $media->isNotEmpty() && $media->every(fn ($item): bool => filled($item->license)), 'paths' => $media->pluck('path')->filter()->values()->all()];
        });
        if ($this->option('missing-only')) {
            $rows = $rows->where('missing', true)->values();
        }
        if ($this->option('placeholder-only')) {
            $rows = $rows->where('placeholder', true)->values();
        }
        $allPaths = $recipes->flatMap(fn (Recipe $recipe) => $recipe->media->filter(fn ($media): bool => (bool) $media->is_placeholder)->pluck('path')->filter());
        $ranked = $rows->sortByDesc(fn (array $row): int => ($row['featured'] ? 20 : 0) + ($row['missing'] ? 10 : 0) + ($row['placeholder'] ? 5 : 0))->values();
        $priority = $ranked->unique('cuisine')->take(10);
        if ($priority->count() < 10) {
            $priority = $priority->concat($ranked->reject(fn (array $row): bool => $priority->contains('public_id', $row['public_id']))->take(10 - $priority->count()));
        }
        $priority = $priority->values();
        $report = ['total_recipes' => $recipes->count(), 'verified_media_recipes' => $rows->where('verified', true)->count(), 'placeholder_recipes' => $rows->where('placeholder', true)->count(), 'missing_media_recipes' => $rows->where('missing', true)->count(), 'repeated_placeholder_paths' => $allPaths->countBy()->filter(fn (int $count): bool => $count > 1)->count(), 'most_repeated_placeholder_count' => $allPaths->countBy()->max() ?? 0, 'cuisine_coverage' => $rows->groupBy(fn (array $row): string => $row['cuisine'] ?? 'Belirsiz')->map(fn ($group): int => $group->count())->all(), 'category_coverage' => $rows->groupBy(fn (array $row): string => $row['category'] ?? 'Belirsiz')->map(fn ($group): int => $group->count())->all(), 'alt_complete_recipes' => $rows->where('alt_complete', true)->count(), 'thumbnail_complete_recipes' => $rows->where('thumbnail_complete', true)->count(), 'license_complete_recipes' => $rows->where('license_complete', true)->count(), 'priority_pilot' => $priority->all(), 'recipes' => $rows->all()];
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('Tarif: '.$report['total_recipes'].' · gerçek/doğrulanmış: '.$report['verified_media_recipes'].' · yer tutucu: '.$report['placeholder_recipes'].' · eksik: '.$report['missing_media_recipes']);
            $this->line('Tekrarlanan yer tutucu: '.$report['repeated_placeholder_paths'].' yol · en yüksek tekrar: '.$report['most_repeated_placeholder_count']);
            $this->table(['Tarif', 'Mutfak', 'Öncelik'], $priority->map(fn (array $row): array => [$row['title'], $row['cuisine'] ?? '—', $row['placeholder'] ? 'Yer tutucu' : 'Eksik'])->all());
        }

        return self::SUCCESS;
    }
}
