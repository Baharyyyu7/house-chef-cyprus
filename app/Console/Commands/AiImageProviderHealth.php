<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\ImageProviderRegistry;
use Illuminate\Console\Command;

final class AiImageProviderHealth extends Command
{
    protected $signature = 'ai-images:provider-health {--live : Gemini model envanterini read-only doğrula} {--json : Makine okunabilir çıktı}';

    protected $description = 'AI görsel sağlayıcılarının güvenli capability durumunu raporlar';

    public function handle(ImageProviderRegistry $providers): int
    {
        $report = $providers->health((bool) $this->option('live'));
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->table(['Provider', 'Yapılandırma', 'Durum', 'Model', 'Not'], collect($report)->map(fn (array $provider): array => [$provider['name'], $provider['configured'] ? 'var' : 'yok', $provider['image_capability'], $provider['model'] ?? '—', $provider['safe_message']])->all());

        return self::SUCCESS;
    }
}
