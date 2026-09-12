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
        $result = $service->run((int) $this->option('limit'));
        $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $result['status'] === 'incomplete' ? self::FAILURE : self::SUCCESS;
    }
}
