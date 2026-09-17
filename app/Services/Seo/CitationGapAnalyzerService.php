<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\AiMention;
use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Str;

final class CitationGapAnalyzerService
{
    /** @return array{gaps:list<array{query:string,cited_sources:list<string>,missing_from:list<string>,opportunity:string}>,summary:array{total_queries:int,cited_count:int,gap_count:int}} */
    public function analyze(): array
    {
        $mentions = AiMention::query()->where('mentioned', true)->latest('checked_at')->limit(50)->get();
        $gaps = [];
        $citedCount = 0;

        foreach ($mentions as $mention) {
            $sources = $mention->cited_sources ?? [];
            $competitors = $mention->competitor_mentions ?? [];

            if (empty($sources) && ! empty($competitors)) {
                $gaps[] = [
                    'query' => Str::limit($mention->prompt, 80),
                    'cited_sources' => $competitors,
                    'missing_from' => ['housechefcyprus.com'],
                    'opportunity' => $this->suggestOpportunity($mention->prompt, $competitors),
                ];
            } elseif (! empty($sources)) {
                $citedCount++;
            }
        }

        $totalQueries = AiMention::query()->count();

        return [
            'gaps' => $gaps,
            'summary' => [
                'total_queries' => $totalQueries,
                'cited_count' => $citedCount,
                'gap_count' => count($gaps),
            ],
        ];
    }

    private function suggestOpportunity(string $prompt, array $competitors): string
    {
        $promptLower = Str::lower($prompt);

        if (str_contains($promptLower, 'tarif') || str_contains($promptLower, 'nasıl yapılır')) {
            return 'Bu soruya özel bir tarif sayfası oluştur veya mevcut tarifi AI-okunabilir formata getir.';
        }

        if (str_contains($promptLower, 'kıbrıs') || str_contains($promptLower, 'cypriot')) {
            return 'Kıbrıs mutfağı ile ilgili kapsamlı bir rehber yazısı oluştur.';
        }

        return 'Bu konuda rakiplerin sunduğu kanıta dayalı içerik üret.';
    }
}
