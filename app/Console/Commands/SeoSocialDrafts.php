<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SocialDraftService;
use Illuminate\Console\Command;

final class SeoSocialDrafts extends Command
{
    protected $signature = 'seo:social-drafts {--dry-run} {--json}';

    protected $description = 'Onaylı içerikler için yalnız taslak sosyal metin üretir; otomatik paylaşım yapmaz.';

    public function handle(SocialDraftService $service): int
    {
        $result = $service->createDrafts((bool) $this->option('dry-run'));
        $payload = $result + ['dry_run' => (bool) $this->option('dry-run'), 'auto_posted' => false];
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info("Sosyal taslaklar: {$result['created']} yeni, {$result['skipped']} atlandı. Paylaşım yapılmadı.");

        return self::SUCCESS;
    }
}
