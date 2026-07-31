<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;

final class ContentRefreshService
{
    /** @return array{created:int,skipped:int} */
    public function createReviewDrafts(bool $dryRun = false): array
    {
        $created = 0;
        $skipped = 0;

        BlogPost::query()->publiclyVisible()->whereNotNull('review_due_at')->where('review_due_at', '<=', now())->each(function (BlogPost $post) use (&$created, &$skipped, $dryRun): void {
            $existing = $post->revisions()->whereNull('reviewed_at')->exists();
            if ($existing) {
                $skipped++;

                return;
            }
            if (! $dryRun) {
                $post->revisions()->create([
                    'revision_number' => ((int) $post->revisions()->max('revision_number')) + 1,
                    'reason' => 'Planlı içerik tazeleme incelemesi',
                    'previous_body' => $post->body,
                ]);
            }
            $created++;
        });

        return compact('created', 'skipped');
    }
}
