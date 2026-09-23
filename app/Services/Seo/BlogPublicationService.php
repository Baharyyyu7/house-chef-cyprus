<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;

final class BlogPublicationService
{
    public function __construct(
        private readonly BlogPublicationReadinessService $readiness,
        private readonly ContentSimilarityService $similarity,
    ) {}

    /** @return array{published:bool,issues:list<string>} */
    public function publish(BlogPost $post): array
    {
        $assessment = $this->readiness->evaluate($post);
        $issues = $assessment['issues'];

        if ($assessment['passed']) {
            $similarity = $this->similarity->inspect($post);
            if ($similarity['status'] !== 'passed') {
                $issues[] = 'İçerik benzerliği editoryal inceleme gerektiriyor.';
            }
        }

        if ($issues !== []) {
            return ['published' => false, 'issues' => array_values(array_unique($issues))];
        }

        $post->update(['status' => 'published', 'published_at' => now(), 'scheduled_at' => null]);

        return ['published' => true, 'issues' => []];
    }

    /** @return array{published:int,skipped:int,reasons:array<string,int>} */
    public function publishAllReady(): array
    {
        $result = ['published' => 0, 'skipped' => 0, 'reasons' => []];

        BlogPost::query()
            ->whereNotIn('status', ['published', 'archived'])
            ->orderBy('id')
            ->chunkById(100, function ($posts) use (&$result): void {
                foreach ($posts as $post) {
                    $publication = $this->publish($post);
                    if ($publication['published']) {
                        $result['published']++;
                        continue;
                    }

                    $result['skipped']++;
                    foreach ($publication['issues'] as $issue) {
                        $result['reasons'][$issue] = ($result['reasons'][$issue] ?? 0) + 1;
                    }
                }
            });

        arsort($result['reasons']);

        return $result;
    }
}
