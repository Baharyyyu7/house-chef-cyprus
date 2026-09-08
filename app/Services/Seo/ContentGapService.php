<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\Recipe;
use App\Models\ZeroResultSearch;
use Illuminate\Support\Str;

final class ContentGapService
{
    /** @return array{created:int,updated:int,opportunities:list<ContentOpportunity>} */
    public function refresh(): array
    {
        $created = 0;
        $updated = 0;
        $opportunities = collect();
        $clusters = ['Kıbrıs Mutfağı', 'Ev Ölçüleri', 'Pişirme Teknikleri', 'Saklama ve Gıda Güvenliği', 'Dolaptakilerle Yemek'];
        $existing = BlogPost::query()->pluck('title')->map(fn (string $title): string => Str::lower($title))->all();

        foreach ($clusters as $cluster) {
            $title = $cluster.' için ev mutfağı rehberi';
            if (collect($existing)->contains(fn (string $item): bool => str_contains($item, Str::lower($cluster)))) {
                continue;
            }
            $opportunity = ContentOpportunity::query()->firstOrNew(['normalized_key' => Str::slug('cluster-'.$cluster)]);
            if ($opportunity->exists && ($opportunity->status !== 'open' || $opportunity->reviewed_at !== null)) {
                continue;
            }
            $wasCreated = ! $opportunity->exists;
            $opportunity->fill(['kind' => 'pillar_guide', 'title' => $title, 'search_intent' => 'informational', 'topic_cluster' => $cluster, 'evidence' => $this->editorialEvidence(['source' => 'platform_catalogue', 'recipe_count' => Recipe::query()->where('status', 'published')->count()]), 'risk_level' => $cluster === 'Saklama ve Gıda Güvenliği' ? 'medium' : 'low', 'priority' => 70, 'status' => 'open']);
            $opportunity->save();
            $wasCreated ? $created++ : $updated++;
            $opportunities->push($opportunity);
        }

        ZeroResultSearch::query()->whereNull('resolved_at')->where('search_count', '>=', (int) config('seo.audit.minimum_zero_result_searches', 5))->orderByDesc('search_count')->limit(10)->each(function (ZeroResultSearch $query) use (&$created, &$updated, $opportunities): void {
            $opportunity = ContentOpportunity::query()->firstOrNew(['normalized_key' => 'zero-'.$query->normalized_query]);
            if ($opportunity->exists && ($opportunity->status !== 'open' || $opportunity->reviewed_at !== null)) {
                return;
            }
            $wasCreated = ! $opportunity->exists;
            $opportunity->fill(['kind' => 'zero_result_question', 'title' => '"'.$query->normalized_query.'" sorusuna yardımcı içerik', 'search_intent' => 'informational', 'topic_cluster' => 'Kullanıcı Soruları', 'evidence' => $this->editorialEvidence(['anonymous_search_count' => $query->search_count]), 'risk_level' => 'low', 'priority' => min(100, 50 + $query->search_count), 'status' => 'open']);
            $opportunity->save();
            $wasCreated ? $created++ : $updated++;
            $opportunities->push($opportunity);
        });

        return ['created' => $created, 'updated' => $updated, 'opportunities' => $opportunities->all()];
    }

    /** @param array<string, int|string> $evidence @return array<string, bool|int|string> */
    private function editorialEvidence(array $evidence): array
    {
        return [
            ...$evidence,
            'locale' => (string) config('seo.locale', 'tr'),
            'collected_at' => now()->toIso8601String(),
            'publication_requires_human_review' => true,
            'delivery_policy' => 'draft_only',
        ];
    }
}
