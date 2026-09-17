<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\AiMention;
use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\SeoIssue;
use App\Models\Recipe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class WeeklySeoReportService
{
    public function generate(): array
    {
        $period = [
            'start' => now()->subWeek()->startOfDay(),
            'end' => now()->endOfDay(),
        ];

        return [
            'period' => $period,
            'content' => $this->contentMetrics($period),
            'ai_visibility' => $this->aiVisibilityMetrics($period),
            'issues' => $this->issueMetrics(),
            'opportunities' => $this->opportunityMetrics(),
            'next_actions' => $this->suggestNextActions(),
        ];
    }

    private function contentMetrics(array $period): array
    {
        $publishedRecipes = Recipe::query()->where('status', 'published')
            ->whereBetween('created_at', [$period['start'], $period['end']])->count();

        $publishedPosts = BlogPost::query()->where('editorial_status', 'published')
            ->whereBetween('created_at', [$period['start'], $period['end']])->count();

        $totalRecipes = Recipe::query()->where('status', 'published')->count();
        $totalPosts = BlogPost::query()->where('editorial_status', 'published')->count();

        return [
            'new_recipes' => $publishedRecipes,
            'new_posts' => $publishedPosts,
            'total_recipes' => $totalRecipes,
            'total_posts' => $totalPosts,
        ];
    }

    private function aiVisibilityMetrics(array $period): array
    {
        $totalChecks = AiMention::query()->whereBetween('created_at', [$period['start'], $period['end']])->count();
        $mentions = AiMention::query()->mentioned()->whereBetween('created_at', [$period['start'], $period['end']])->count();
        $unlinked = AiMention::query()->mentioned()->where('linked', false)
            ->whereBetween('created_at', [$period['start'], $period['end']])->count();

        $byEngine = AiMention::query()->whereBetween('created_at', [$period['start'], $period['end']])
            ->selectRaw('engine, count(*) as cnt, sum(case when mentioned then 1 else 0 end) as mention_cnt')
            ->groupBy('engine')
            ->get()
            ->map(fn ($row) => ['total' => $row->cnt, 'mentioned' => $row->mention_cnt])
            ->all();

        return [
            'total_checks' => $totalChecks,
            'mentions' => $mentions,
            'unlinked' => $unlinked,
            'by_engine' => $byEngine,
        ];
    }

    private function issueMetrics(): array
    {
        $open = SeoIssue::query()->whereNull('resolved_at')->count();
        $critical = SeoIssue::query()->whereNull('resolved_at')->where('severity', 'critical')->count();
        $resolved = SeoIssue::query()->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subWeek())->count();

        return compact('open', 'critical', 'resolved');
    }

    private function opportunityMetrics(): array
    {
        $open = ContentOpportunity::query()->where('status', 'open')->count();
        $highPriority = ContentOpportunity::query()->where('status', 'open')->where('priority', '>=', 80)->count();

        return compact('open', 'highPriority');
    }

    /** @return list<array{priority:int,action:string,reason:string}> */
    private function suggestNextActions(): array
    {
        $actions = [];

        $criticalIssues = SeoIssue::query()->whereNull('resolved_at')->where('severity', 'critical')->count();
        if ($criticalIssues > 0) {
            $actions[] = ['priority' => 100, 'action' => "{$criticalIssues} kritik SEO sorunu var. Önce bunları çöz.", 'reason' => 'Kritik sorunlar indexlemeyi doğrudan etkiler.'];
        }

        $unlinkedMentions = AiMention::query()->mentioned()->where('linked', false)->count();
        if ($unlinkedMentions > 0) {
            $actions[] = ['priority' => 80, 'action' => "{$unlinkedMentions} AI mention'ında sitenize link yok. Link eklemek için outreach yapın.", 'reason' => 'AI yanıtlarında marka geçmesi ama link olmaması fırsat kaybıdır.'];
        }

        $staleRecipes = Recipe::query()->where('status', 'published')
            ->where('updated_at', '<', now()->subMonths(3))->count();
        if ($staleRecipes > 0) {
            $actions[] = ['priority' => 60, 'action' => "{$staleRecipes} tarif 3 aydır güncellenmemiş. Tazeleme planı oluştur.", 'reason' => 'Güncel içerik AI tarafından tercih edilir.'];
        }

        if (empty($actions)) {
            $actions[] = ['priority' => 0, 'action' => 'Kritik aksiyon yok. İçerik üretimi ve AI takibine devam edin.', 'reason' => 'Her şey yolunda görünüyor.'];
        }

        usort($actions, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return $actions;
    }
}
