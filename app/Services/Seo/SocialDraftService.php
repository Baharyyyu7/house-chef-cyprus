<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\SocialDistributionDraft;
use Illuminate\Support\Str;

final class SocialDraftService
{
    /** @return array{created:int,skipped:int} */
    public function createDrafts(bool $dryRun = false): array
    {
        $created = 0;
        $skipped = 0;
        $limit = (int) config('seo.max_social_drafts_per_day', 10);

        BlogPost::query()->publiclyVisible()->latest('published_at')->limit($limit)->each(function (BlogPost $post) use (&$created, &$skipped, $dryRun): void {
            if ($post->socialDrafts()->where('channel', 'x')->exists()) {
                $skipped++;

                return;
            }
            if (! $dryRun) {
                SocialDistributionDraft::query()->create([
                    'blog_post_id' => $post->id,
                    'channel' => 'x',
                    'body' => Str::limit($post->title.' — '.$post->excerpt.' '.route('blog.show', $post), 260, ''),
                    'safe_metadata' => ['source' => 'approved_blog_post', 'auto_posting' => false],
                    'status' => 'draft',
                ]);
            }
            $created++;
        });

        return compact('created', 'skipped');
    }
}
