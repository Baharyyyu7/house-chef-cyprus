<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Seo\BlogPublicationReadinessService;
use Illuminate\Console\Command;

final class PublishScheduledBlogPosts extends Command
{
    protected $signature = 'blog:publish-scheduled {--json : Makine okunabilir çıktı üretir}';

    protected $description = 'Zamanı gelen blog taslaklarını güvenli şekilde yayınlar.';

    public function handle(BlogPublicationReadinessService $readiness): int
    {
        $count = 0;
        BlogPost::query()->where('status', 'scheduled')->whereNotNull('scheduled_at')->where('scheduled_at', '<=', now())
            ->get()->each(function (BlogPost $post) use ($readiness, &$count): void {
                if (! $readiness->evaluate($post)['passed']) {
                    return;
                }
                $post->update(['status' => 'published', 'published_at' => now()]);
                $count++;
            });
        $result = ['published' => $count, 'at' => now()->toIso8601String()];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info($count.' zamanlanmış blog yazısı yayınlandı.');
        }

        return self::SUCCESS;
    }
}
