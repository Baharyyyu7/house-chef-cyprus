<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

final class PublishScheduledBlogPosts extends Command
{
    protected $signature = 'blog:publish-scheduled {--json : Makine okunabilir çıktı üretir}';

    protected $description = 'Zamanı gelen blog taslaklarını güvenli şekilde yayınlar.';

    public function handle(): int
    {
        $count = BlogPost::query()->where('status', 'scheduled')->whereNotNull('scheduled_at')->where('scheduled_at', '<=', now())
            ->update(['status' => 'published', 'published_at' => now()]);
        $result = ['published' => $count, 'at' => now()->toIso8601String()];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info($count.' zamanlanmış blog yazısı yayınlandı.');
        }

        return self::SUCCESS;
    }
}
