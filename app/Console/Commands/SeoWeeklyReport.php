<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\WeeklySeoReportService;
use Illuminate\Console\Command;

final class SeoWeeklyReport extends Command
{
    protected $signature = 'seo:weekly-report {--json : Output as JSON} {--save : Save report to storage}';

    protected $description = 'Generate comprehensive weekly SEO performance report';

    public function handle(WeeklySeoReportService $reportService): int
    {
        $this->info('Generating weekly SEO report...');

        $report = $reportService->generate();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('=== Weekly SEO Report ===');
        $this->info('Period: '.$report['period']['start']->format('d M Y').' - '.$report['period']['end']->format('d M Y'));
        $this->newLine();

        $this->info('--- Content Metrics ---');
        $this->table(
            ['Metric', 'Value'],
            [
                ['New Recipes', $report['content']['new_recipes']],
                ['New Blog Posts', $report['content']['new_posts']],
                ['Total Recipes', $report['content']['total_recipes']],
                ['Total Blog Posts', $report['content']['total_posts']],
            ]
        );

        $this->newLine();
        $this->info('--- AI Visibility ---');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Checks', $report['ai_visibility']['total_checks']],
                ['Brand Mentions', $report['ai_visibility']['mentions']],
                ['Unlinked Mentions', $report['ai_visibility']['unlinked']],
            ]
        );

        if (! empty($report['ai_visibility']['by_engine'])) {
            $this->newLine();
            $this->info('By Engine:');
            foreach ($report['ai_visibility']['by_engine'] as $engine => $data) {
                $this->line("  {$engine}: {$data['mentioned']}/{$data['total']} mentioned");
            }
        }

        $this->newLine();
        $this->info('--- Technical Issues ---');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Open Issues', $report['issues']['open']],
                ['Critical Issues', $report['issues']['critical']],
                ['Resolved This Week', $report['issues']['resolved']],
            ]
        );

        $this->newLine();
        $this->info('--- Opportunities ---');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Open Opportunities', $report['opportunities']['open']],
                ['High Priority', $report['opportunities']['highPriority']],
            ]
        );

        $this->newLine();
        $this->info('--- Recommended Next Actions ---');
        foreach ($report['next_actions'] as $action) {
            $priority = $action['priority'] >= 80 ? '[CRITICAL]' : ($action['priority'] >= 60 ? '[HIGH]' : '[NORMAL]');
            $this->line(" {$priority} {$action['action']}");
            $this->line("   Reason: {$action['reason']}");
        }

        if ($this->option('save')) {
            $path = storage_path('app/reports/seo-weekly-'.now()->format('Y-m-d').'.json');
            $dir = dirname($path);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
            $this->info("Report saved to: {$path}");
        }

        return self::SUCCESS;
    }
}
