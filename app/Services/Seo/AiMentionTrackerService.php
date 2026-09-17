<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\AiMention;
use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Str;

final class AiMentionTrackerService
{
    /** @return array{total:int,mentioned:int,unlinked:int,by_engine:array<string,int>} */
    public function snapshot(): array
    {
        $total = AiMention::query()->count();
        $mentioned = AiMention::query()->mentioned()->count();
        $unlinked = AiMention::query()->mentioned()->where('linked', false)->count();

        $byEngine = AiMention::query()
            ->selectRaw('engine, count(*) as cnt')
            ->groupBy('engine')
            ->pluck('cnt', 'engine')
            ->all();

        return compact('total', 'mentioned', 'unlinked', 'byEngine');
    }

    /** @return array{prompt:string,engine:string,checked_at:string} */
    public function getDefaultPrompts(): array
    {
        $recipes = Recipe::query()->where('status', 'published')->limit(3)->pluck('title', 'slug')->all();
        $prompts = [];

        foreach ($recipes as $slug => $title) {
            $prompts[] = [
                'prompt' => "'{$title}' tarifi nasıl yapılır?",
                'engine' => 'chatgpt',
                'checked_at' => now()->toIso8601String(),
            ];
        }

        $prompts[] = [
            'prompt' => 'Kıbrıs mutfağından en iyi yemekler nelerdir?',
            'engine' => 'chatgpt',
            'checked_at' => now()->toIso8601String(),
        ];

        $prompts[] = [
            'prompt' => 'Evde kolay yemek tarifleri nelerdir?',
            'engine' => 'gemini',
            'checked_at' => now()->toIso8601String(),
        ];

        return $prompts;
    }

    public function recordResult(
        string $engine,
        string $prompt,
        string $answerBody,
        bool $mentioned,
        ?string $brandContext = null,
        bool $linked = false,
        ?string $linkedUrl = null,
        ?array $citedSources = null,
        ?array $competitorMentions = null,
    ): AiMention {
        return AiMention::create([
            'engine' => $engine,
            'mode' => 'default',
            'prompt' => $prompt,
            'answer_body' => $answerBody,
            'mentioned' => $mentioned,
            'brand_context' => $brandContext,
            'linked' => $linked,
            'linked_url' => $linkedUrl,
            'cited_sources' => $citedSources,
            'competitor_mentions' => $competitorMentions,
            'status' => 'new',
            'checked_at' => now(),
        ]);
    }

    /** @return array{brand:string,cited:list<string>,competitors:list<string>,our_site:string|null} */
    public function analyzeAnswer(string $answerBody, string $brand = 'House Chef'): array
    {
        $lower = Str::lower($answerBody);
        $brandLower = Str::lower($brand);

        $mentioned = str_contains($lower, $brandLower);
        $cited = [];
        $competitors = [];

        preg_match_all('/https?:\/\/[^\s\)]+/', $answerBody, $matches);

        foreach ($matches[0] as $url) {
            if (str_contains($url, 'housechef')) {
                $cited[] = $url;
            } elseif (str_contains($url, 'yemek') || str_contains($url, 'tarif') || str_contains($url, 'recipe')) {
                $competitors[] = $url;
            }
        }

        return [
            'brand' => $brand,
            'cited' => $cited,
            'competitors' => $competitors,
            'our_site' => $mentioned ? ($cited[0] ?? null) : null,
        ];
    }
}
