<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PublishedBlogCompletionService;
use Illuminate\Console\Command;

final class PublishedBlogCompletion extends Command
{
    protected $signature = 'content:published-blog-completion {--prepare : Create or refresh private review revisions} {--json : Machine-readable output}';

    protected $description = 'Audit published blogs and optionally prepare private, non-publishing review packs.';

    public function handle(PublishedBlogCompletionService $service): int
    {
        $result = $this->option('prepare') ? $service->prepare() : $service->audit();
        $this->line($this->option('json') ? (string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
