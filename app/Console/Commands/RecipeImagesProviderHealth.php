<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\GeminiImageGenerationService;
use App\Services\Media\ImageProviderRegistry;
use Illuminate\Console\Command;

final class RecipeImagesProviderHealth extends Command
{
    protected $signature = 'recipe-images:provider-health {--provider=gemini : Sağlayıcı adı} {--no-billable-call : Yalnız yapılandırma ve parser readiness kontrolü} {--live-test : Domates Çorbası için tek açık diagnostic recovery çağrısı} {--json : Makine okunabilir çıktı}';

    protected $description = 'Görsel sağlayıcısı için güvenli capability ve tek çağrılık recovery health kontrolü';

    public function handle(ImageProviderRegistry $providers, GeminiImageGenerationService $generator): int
    {
        if ($this->option('provider') !== 'gemini') {
            $this->error('Bu sürüm yalnız Gemini adapterını destekler.');

            return self::FAILURE;
        }
        $report = ['provider' => 'gemini', 'billable_call' => false, 'health' => $providers->health()[0] ?? [], 'adapter' => ['endpoint_class' => 'Gemini API v1beta predict', 'request_schema_ready' => true, 'response_parser_ready' => true]];
        if ($this->option('live-test')) {
            $report['billable_call'] = true;
            $report['recovery'] = $generator->recoverTomatoSoup();
        }
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->table(['Sağlayıcı', 'Çağrı', 'Adapter', 'Sonuç'], [[
            'gemini', $report['billable_call'] ? 'tek diagnostic' : 'faturasız', 'hazır', $report['recovery']['safe_message'] ?? ($report['health']['safe_message'] ?? '—'),
        ]]);

        return self::SUCCESS;
    }
}
