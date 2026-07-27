<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\RecipeCompletenessService;
use Illuminate\Console\Command;

final class RecipeCompletenessReport extends Command
{
    protected $signature = 'recipes:completeness-report {--json}';

    protected $description = 'Tarif tamlık skorlarını ve yayınlanabilirlik özetini gösterir.';

    public function handle(RecipeCompletenessService $completeness): int
    {
        $rows = Recipe::query()->orderBy('title')->get()->map(function (Recipe $recipe) use ($completeness): array {
            $result = $completeness->refresh($recipe);

            return ['public_id' => $recipe->public_id, 'title' => $recipe->title, 'score' => $result['score'], 'status' => $result['status'], 'verification_status' => $recipe->verification_status];
        });
        $summary = ['total' => $rows->count(), 'publishable' => $rows->where('status', 'publishable')->count(), 'verified' => $rows->where('verification_status', 'verified')->count(), 'recipes' => $rows->values()];
        if ($this->option('json')) {
            $this->line((string) json_encode($summary, JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Tarif', 'Skor', 'Tamlık', 'Doğrulama'], $rows->map(fn (array $row): array => [$row['title'], $row['score'], $row['status'], $row['verification_status']])->all());
            $this->info('Toplam: '.$summary['total'].' · Yayınlanabilir: '.$summary['publishable'].' · Doğrulanmış: '.$summary['verified']);
        }

        return self::SUCCESS;
    }
}
