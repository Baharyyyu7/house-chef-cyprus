<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\RecipeImageProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class StabilityRecipeImageProvider implements RecipeImageProviderInterface
{
    public function __construct(private readonly ProviderImageResponseParser $parser) {}

    public function name(): string
    {
        return 'stability';
    }

    public function health(): array
    {
        $settings = (array) config('ai_images.providers.stability');
        $configured = filled($settings['key'] ?? null);
        $enabled = (bool) ($settings['enabled'] ?? false);

        return ['name' => $this->name(), 'configured' => $configured, 'enabled' => $enabled, 'image_capability' => ! $configured ? 'not_configured' : (! $enabled ? 'disabled_pending_cost_approval' : 'enabled_requires_live_test'), 'model' => $settings['model'] ?? null, 'safe_message' => ! $configured ? 'Stability API anahtarı yapılandırılmadı.' : (! $enabled ? 'Stability canlı üretimi kapalı.' : 'Stability canlı test için hazır; tek çağrı limiti uygulanır.')];
    }

    public function generate(string $prompt, string $negativePrompt, string $aspectRatio): ProviderImageResult
    {
        $settings = (array) config('ai_images.providers.stability');
        if (! $this->health()['configured'] || ! $this->health()['enabled']) {
            return new ProviderImageResult('provider_blocked', 'IMAGE_PROVIDER_NOT_CONFIGURED', 'Stability canlı üretim için yapılandırılmadı.');
        }
        $payload = ['prompt' => $prompt, 'negative_prompt' => $negativePrompt, 'aspect_ratio' => $aspectRatio, 'output_format' => 'webp'];
        $started = hrtime(true);
        try {
            $response = Http::withToken((string) $settings['key'])->accept('image/*')->asMultipart()->timeout(45)
                ->post(rtrim((string) $settings['endpoint'], '/').'/v2beta/stable-image/generate/core', $payload);
        } catch (ConnectionException) {
            return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_TIMEOUT', 'Stability isteği güvenli zaman aşımı sınırını geçti.');
        }

        return $this->parser->parse($response, strlen((string) json_encode($payload)), (int) ((hrtime(true) - $started) / 1_000_000), 'stability');
    }
}
