<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\AutonomousMediaGateService;
use App\Services\Seo\AutonomousPublishingGateService;
use Illuminate\Console\Command;

final class SeoDripPublish extends Command
{
    protected $signature = 'seo:drip-publish 
                            {--dry-run : Test quality gates without modifying database}
                            {--blogs=2 : Maximum blog posts to publish in this cycle}
                            {--recipes=3 : Maximum recipes to publish in this cycle}';

    protected $description = 'Autonomous quality gate & drip-feed publishing for verified blog posts and recipes';

    public function handle(
        AutonomousMediaGateService $mediaGate,
        AutonomousPublishingGateService $publishingGate
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $blogLimit = (int) $this->option('blogs');
        $recipeLimit = (int) $this->option('recipes');

        $this->info('Starting Autonomous Quality Gate & Drip Publishing Cycle...');

        // Step 1: Process and promote qualifying media candidates
        $mediaResult = $mediaGate->processEligibleCandidates(10);
        $this->line("• Medya Kapısı: {$mediaResult['approved']} onaylandı, {$mediaResult['rejected']} reddedildi, {$mediaResult['skipped']} atlandı.");

        // Step 2: Evaluate and drip publish content
        $publishResult = $publishingGate->run($blogLimit, $recipeLimit, $dryRun);

        $this->line("• Yayınlanan Bloglar: " . count($publishResult['published_blogs']) . " (" . implode(', ', $publishResult['published_blogs']) . ")");
        $this->line("• Yayınlanan Tarifler: " . count($publishResult['published_recipes']) . " (" . implode(', ', $publishResult['published_recipes']) . ")");

        if (! empty($publishResult['held_back_blogs'])) {
            $this->warn("• Bekletilen Bloglar: " . count($publishResult['held_back_blogs']));
            foreach ($publishResult['held_back_blogs'] as $item) {
                $this->line("  - [{$item['slug']}]: {$item['reason']}");
            }
        }

        if (! empty($publishResult['held_back_recipes'])) {
            $this->warn("• Bekletilen Tarifler: " . count($publishResult['held_back_recipes']));
            foreach ($publishResult['held_back_recipes'] as $item) {
                $this->line("  - [{$item['slug']}]: {$item['reason']}");
            }
        }

        $this->info('✓ Otonom yayınlama döngüsü başarıyla tamamlandı.');

        return self::SUCCESS;
    }
}
