<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\PublicBlogMediaCompletionService;
use Illuminate\Console\Command;

final class PublicBlogMediaCompletionAudit extends Command
{
    protected $signature = 'blog-media:completion-audit {--probe-public : Güvenli HTTP HEAD kontrolü} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yayınlı blogların medya, responsive variant ve public URL envanterini yalnız-okunur üretir.';

    public function handle(PublicBlogMediaCompletionService $service): int
    {
        $report = $service->audit((bool) $this->option('probe-public'));
        $this->line($this->option('json')
            ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : 'Blog: '.$report['published_blogs_audited'].' · hazır: '.$report['approved_media'].' · placeholder: '.$report['gradient_placeholders']);

        return self::SUCCESS;
    }
}
