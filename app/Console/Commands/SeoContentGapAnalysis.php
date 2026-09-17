<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\KeywordTopic;
use App\Models\Recipe;
use App\Models\ZeroResultSearch;
use App\Services\Seo\ContentGapService;
use Illuminate\Console\Command;

final class SeoContentGapAnalysis extends Command
{
    protected $signature = 'seo:content-gap-analysis {--json : Output as JSON}';

    protected $description = 'Analyze content gaps and generate prioritized opportunity report';

    public function handle(ContentGapService $gapService): int
    {
        $this->info('Running content gap analysis...');

        $result = $gapService->refresh();

        $stats = [
            'total_recipes' => Recipe::query()->where('status', 'published')->count(),
            'total_blog_posts' => BlogPost::query()->where('editorial_status', 'published')->count(),
            'total_keyword_topics' => KeywordTopic::query()->count(),
            'total_content_opportunities' => ContentOpportunity::query()->count(),
            'open_opportunities' => ContentOpportunity::query()->where('status', 'open')->count(),
            'zero_result_searches' => ZeroResultSearch::query()->whereNull('resolved_at')->count(),
            'opportunities_created' => $result['created'],
            'opportunities_updated' => $result['updated'],
        ];

        $topOpportunities = ContentOpportunity::query()
            ->where('status', 'open')
            ->orderByDesc('priority')
            ->limit(20)
            ->get()
            ->map(fn (ContentOpportunity $o) => [
                'title' => $o->title,
                'kind' => $o->kind,
                'priority' => $o->priority,
                'topic_cluster' => $o->topic_cluster,
                'search_intent' => $o->search_intent,
            ]);

        if ($this->option('json')) {
            $this->line(json_encode([
                'stats' => $stats,
                'top_opportunities' => $topOpportunities,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('=== Content Gap Analysis ===');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            array_map(fn ($key, $value) => [$key, $value], array_keys($stats), array_values($stats))
        );

        $this->newLine();
        $this->info('Top 20 Opportunities by Priority:');
        $this->newLine();

        $this->table(
            ['Title', 'Kind', 'Priority', 'Cluster', 'Intent'],
            $topOpportunities->toArray()
        );

        return self::SUCCESS;
    }
}
