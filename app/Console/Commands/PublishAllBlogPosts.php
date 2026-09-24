<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

final class PublishAllBlogPosts extends Command
{
    protected $signature = 'blog:publish-all {--force : Onay sormadan çalıştırır}';

    protected $description = 'Arşivlenmemiş ve henüz yayınlanmamış tüm blog yazılarını yayınlar.';

    public function handle(): int
    {
        $query = BlogPost::query()->whereNotIn('status', ['published', 'archived']);
        $pending = $query->count();
        if ($pending === 0) {
            $this->info('Yayınlanacak blog yazısı yok.');

            return self::SUCCESS;
        }
        if (! $this->option('force') && ! $this->confirm($pending.' blog yazısı yayınlanacak. Devam edilsin mi?')) {
            return self::FAILURE;
        }
        $count = $query->update(['status' => 'published', 'published_at' => now(), 'scheduled_at' => null]);
        $this->info($count.' blog yazısı yayınlandı.');

        return self::SUCCESS;
    }
}
