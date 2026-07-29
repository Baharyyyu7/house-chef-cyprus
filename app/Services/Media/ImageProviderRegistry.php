<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\ImageGenerationProviderInterface;
use App\Contracts\Media\RecipeImageProviderInterface;
use App\Models\AiImageGeneration;

final class ImageProviderRegistry
{
    /** @return list<array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string}> */
    public function health(bool $live = false): array
    {
        $providers = collect(config('ai_images.providers', []))->map(function (array $provider, string $name): array {
            $configured = filled($provider['key'] ?? null);
            $enabled = (bool) ($provider['enabled'] ?? false);

            return [
                'name' => $name,
                'configured' => $configured,
                'enabled' => $enabled,
                'image_capability' => ! $configured ? 'disabled_missing_configuration' : (! $enabled ? 'disabled_pending_cost_approval' : 'enabled_requires_live_test'),
                'model' => isset($provider['model']) ? (string) $provider['model'] : null,
                'safe_message' => ! $configured
                    ? 'Yapılandırma bulunamadı.'
                    : (! $enabled ? 'Görsel üretimi maliyet onayı beklediği için kapalı.' : 'Canlı görsel üretim testi henüz çalıştırılmadı.'),
            ];
        })->values()->all();

        $lastRecovery = AiImageGeneration::query()->where('provider', 'gemini')->where('generation_scope', 'diagnostic_recovery')->latest()->first();
        if ($lastRecovery?->error_code === 'IMAGE_PROVIDER_MODEL_UNAVAILABLE' || config('ai_images.providers.gemini.pilot_status') === 'unavailable_for_pilot') {
            $providers = collect($providers)->map(fn (array $provider): array => $provider['name'] === 'gemini'
                ? array_merge($provider, ['image_capability' => 'unavailable_model_or_endpoint', 'safe_message' => 'Tek diagnostic recovery çağrısı model veya endpointin bu kurulumda kullanılamadığını doğruladı.'])
                : $provider)->values()->all();
        }
        if ($live) {
            $gemini = app(ImageGenerationProviderInterface::class)->health(true);
            $providers = collect($providers)->map(fn (array $provider): array => $provider['name'] === 'gemini' ? $gemini : $provider)->values()->all();
        }

        $providers = collect($providers)->map(function (array $provider): array {
            return match ($provider['name']) {
                'edenai' => app(EdenAiRecipeImageProvider::class)->health(),
                'pexels' => app(PexelsRecipeImageProvider::class)->health(),
                'bfl', 'stability', 'comfyui' => $this->fallback($provider['name'])->health(),
                default => $provider,
            };
        })->values()->all();

        return $providers;
    }

    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string} */
    public function providerHealth(string $name): array
    {
        if ($name === 'gemini') {
            return app(ImageGenerationProviderInterface::class)->health(false);
        }
        if ($name === 'edenai') {
            return app(EdenAiRecipeImageProvider::class)->health();
        }
        if ($name === 'pexels') {
            return app(PexelsRecipeImageProvider::class)->health();
        }

        return $this->fallback($name)->health();
    }

    private function fallback(string $name): RecipeImageProviderInterface
    {
        return match ($name) {
            'bfl' => app(BflRecipeImageProvider::class),
            'stability' => app(StabilityRecipeImageProvider::class),
            'comfyui' => app(ComfyUiRecipeImageProvider::class),
            default => throw new \InvalidArgumentException('Unsupported image provider.'),
        };
    }
}
