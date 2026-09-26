<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;

final class BlogDraftReadinessSnapshotService
{
    public function __construct(private readonly BlogPublicationReadinessService $readiness) {}

    /** @return array{total:int,ready:int,blocked:int,reasons:array<string,int>,samples:list<array{public_id:string,title:string,issues:list<string>}>} */
    public function snapshot(int $sampleLimit = 20): array
    {
        $result = ['total' => 0, 'ready' => 0, 'blocked' => 0, 'reasons' => [], 'samples' => []];
        $sampleLimit = max(0, min(100, $sampleLimit));

        BlogPost::query()->whereNotIn('status', ['published', 'archived'])->orderBy('id')
            ->chunkById(100, function ($posts) use (&$result, $sampleLimit): void {
                foreach ($posts as $post) {
                    $evaluation = $this->readiness->evaluate($post);
                    $result['total']++;
                    if ($evaluation['passed']) {
                        $result['ready']++;
                        continue;
                    }

                    $result['blocked']++;
                    foreach ($evaluation['issues'] as $issue) {
                        $result['reasons'][$issue] = ($result['reasons'][$issue] ?? 0) + 1;
                    }
                    if (count($result['samples']) < $sampleLimit) {
                        $result['samples'][] = [
                            'public_id' => (string) $post->public_id,
                            'title' => (string) $post->title,
                            'issues' => $evaluation['issues'],
                        ];
                    }
                }
            });

        arsort($result['reasons']);

        return $result;
    }
}
