<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\BacklinkVerificationService;
use Illuminate\Console\Command;

final class SeoBacklinksVerify extends Command
{
    protected $signature = 'seo:backlinks-verify {--limit=3} {--json}';

    protected $description = 'En fazla üç farklı kaynaktaki gerçek bağlantıları doğrular; mesaj göndermez.';

    public function handle(BacklinkVerificationService $service): int
    {
        $this->line(json_encode($service->run((int) $this->option('limit')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
