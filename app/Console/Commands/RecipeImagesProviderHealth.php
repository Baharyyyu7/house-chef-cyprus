<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\FallbackImageComparisonService;
use App\Services\Media\GeminiImageGenerationService;
use App\Services\Media\ImageProviderRegistry;
use App\Services\Media\LocalComfyUiImageGenerationService;
use Illuminate\Console\Command;

final class RecipeImagesProviderHealth extends Command
{
    protected $signature = 'recipe-images:provider-health {--provider=gemini : Sağlayıcı adı} {--no-billable-call : Yalnız yapılandırma ve parser readiness kontrolü} {--live-test : Açık sağlayıcı için sınırlı test} {--json : Makine okunabilir çıktı}';

    protected $description = 'Görsel sağlayıcısı için güvenli capability ve tek çağrılık recovery health kontrolü';

    public function handle(ImageProviderRegistry $providers, GeminiImageGenerationService $generator, FallbackImageComparisonService $comparison, LocalComfyUiImageGenerationService $comfy): int
    {
        $name = (string) $this->option('provider');
        if (! in_array($name, ['gemini', 'edenai', 'pexels', 'unsplash', 'bfl', 'stability', 'comfyui'], true)) {
            $this->error('Desteklenmeyen görsel sağlayıcısı.');

            return self::FAILURE;
        }
        $report = ['provider' => $name, 'billable_call' => false, 'health' => $providers->providerHealth($name), 'adapter' => match ($name) {
            'bfl' => ['endpoint_class' => 'BFL FLUX.2 async polling', 'request_schema_ready' => true, 'response_parser_ready' => true],
            'stability' => ['endpoint_class' => 'Stability REST v2beta multipart', 'request_schema_ready' => true, 'response_parser_ready' => true],
            'comfyui' => ['endpoint_class' => 'Local ComfyUI queue and history polling', 'request_schema_ready' => true, 'response_parser_ready' => true],
            'edenai' => ['endpoint_class' => 'Eden AI universal-ai', 'request_schema_ready' => true, 'response_parser_ready' => true],
            'pexels' => ['endpoint_class' => 'Pexels v1 review candidate search', 'request_schema_ready' => true, 'response_parser_ready' => false],
            'unsplash' => ['endpoint_class' => 'Unsplash official REST hotlink candidate adapter', 'request_schema_ready' => true, 'response_parser_ready' => true],
            default => ['endpoint_class' => 'Gemini API v1beta predict', 'request_schema_ready' => true, 'response_parser_ready' => true],
        }];
        if ($this->option('live-test') && $name === 'gemini') {
            $report['billable_call'] = true;
            $report['recovery'] = $generator->recoverTomatoSoup();
        }
        if ($this->option('live-test') && in_array($name, ['bfl', 'stability'], true)) {
            $report['billable_call'] = true;
            $report['comparison'] = $comparison->run($name)['providers'][$name] ?? [];
        }
        if ($this->option('live-test') && $name === 'comfyui') {
            $report['billable_call'] = false;
            $report['comparison'] = $comfy->generateTomatoSoup();
        }
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $safeMessage = $report['recovery']['safe_message'] ?? $report['comparison']['safe_message'] ?? $report['health']['safe_message'];
        $this->table(['Sağlayıcı', 'Çağrı', 'Adapter', 'Sonuç'], [[
            $name, $report['billable_call'] ? 'sınırlı kontrol' : 'faturasız', $report['adapter']['endpoint_class'], $safeMessage,
        ]]);

        return self::SUCCESS;
    }
}
