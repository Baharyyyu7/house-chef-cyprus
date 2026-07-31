<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\IndexingEvent;
use Illuminate\Console\Command;

final class SeoIndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow-submit {--json}';

    protected $description = 'IndexNow gönderim durumunu güvenli biçimde kontrol eder; kapalı sağlayıcıya istek göndermez.';

    public function handle(): int
    {
        $enabled = (bool) config('seo.indexnow.enabled');
        $payload = ['provider' => 'indexnow', 'enabled' => $enabled, 'submitted' => 0, 'reason' => $enabled ? 'manual_approval_required' : 'provider_disabled'];
        IndexingEvent::query()->create(['url' => route('sitemap'), 'event_type' => 'sitemap_check', 'provider' => 'indexnow', 'status' => $enabled ? 'pending_approval' : 'disabled']);
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->warn('IndexNow otomatik gönderimi kapalıdır; hiçbir dış istekte bulunulmadı.');

        return self::SUCCESS;
    }
}
