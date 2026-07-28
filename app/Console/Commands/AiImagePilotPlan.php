<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\AiImagePilotService;
use App\Services\Media\ImageProviderRegistry;
use Illuminate\Console\Command;

final class AiImagePilotPlan extends Command
{
    protected $signature = 'ai-images:pilot-plan {--sync : Pilot taslaklarını veritabanına yaz} {--json : Makine okunabilir çıktı}';

    protected $description = '10 tariflik AI görsel pilotu için maliyet ve provider güvenlik planını gösterir';

    public function handle(AiImagePilotService $pilot, ImageProviderRegistry $providers): int
    {
        if ($this->option('sync')) {
            $pilot->synchronize();
        }
        $report = ['providers' => $providers->health(), 'cost_preview' => $pilot->costPreview(), 'recipes' => $pilot->recipes()->map(fn ($recipe): array => ['public_id' => (string) $recipe->public_id, 'title' => $recipe->title, 'cuisine' => $recipe->cuisine?->name, 'category' => $recipe->category?->name])->all(), 'generation_allowed' => false, 'safe_message' => 'Gerçek üretim, ayrı maliyet onayı ve canlı image-capability testi olmadan başlatılmaz.'];
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->table(['Tarif', 'Mutfak', 'Kategori'], collect($report['recipes'])->map(fn (array $row): array => [$row['title'], $row['cuisine'], $row['category']])->all());
        $estimated = $report['cost_preview']['estimated_cost_cents'] === null ? 'belirlenmedi' : $report['cost_preview']['estimated_cost_cents'].' cent';
        $this->info('Varyasyon: '.$report['cost_preview']['variation_count'].' · tahmini maliyet: '.$estimated.' · günlük bütçe: '.$report['cost_preview']['budget_cents'].' cent');
        $this->warn($report['safe_message']);

        return self::SUCCESS;
    }
}
