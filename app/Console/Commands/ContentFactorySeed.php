<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContentFactoryService;
use Illuminate\Console\Command;

final class ContentFactorySeed extends Command
{
    protected $signature = 'content:factory-seed {--type=all : recipe, blog veya all} {--daily : Yalnız sonraki eksik batch, günlük limite uygun} {--json}';

    protected $description = 'Yalnız taslak içerik üretir; doğrulama, insan onayı ve yayınlama yapmaz.';

    public function handle(ContentFactoryService $factory): int
    {
        if (! config('content_factory.enabled')) {
            $this->error('İçerik otomasyonu kapalı. CONTENT_AUTOMATION_ENABLED ayarını kontrol edin.');

            return self::FAILURE;
        }
        $type = (string) $this->option('type');
        $daily = (bool) $this->option('daily');
        $result = match ($type) {
            'recipe' => ['recipes' => $daily ? $factory->seedNextRecipeBatch() : $factory->seedRecipeDrafts()],
            'blog' => ['blogs' => $daily ? $factory->seedNextBlogBatch() : $factory->seedBlogDrafts()],
            'all' => $daily ? ['recipes' => $factory->seedNextRecipeBatch(), 'blogs' => $factory->seedNextBlogBatch()] : $factory->seedAll(),
            default => null,
        };
        if ($result === null) {
            $this->error('Geçersiz tür: recipe, blog veya all kullanın.');

            return self::INVALID;
        }
        $payload = array_merge($result, ['daily_mode' => $daily, 'auto_publish' => false, 'human_approval_required' => true]);
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('Taslak içerik işlemi tamamlandı. Otomatik yayın kapalıdır.');

        return self::SUCCESS;
    }
}
