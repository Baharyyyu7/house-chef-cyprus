<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Seo\InternalLinkService;
use Illuminate\Console\Command;

final class SeoInternalLinks extends Command
{
    protected $signature = 'seo:internal-links {--json}';

    protected $description = 'Yalnız editoryal inceleme için açıklanabilir iç bağlantı önerileri listeler.';

    public function handle(InternalLinkService $service): int
    {
        $suggestions = BlogPost::query()->publiclyVisible()->limit(20)->get()->map(fn (BlogPost $post): array => ['post_public_id' => $post->public_id, 'suggestions' => $service->suggestionsFor($post)]);
        $payload = ['count' => $suggestions->count(), 'auto_inserted' => false, 'suggestions' => $suggestions];
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('İç bağlantı önerileri hazır; otomatik bağlantı eklenmedi.');

        return self::SUCCESS;
    }
}
