<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\ImageGenerationProviderInterface;

final class ImageProviderRegistry
{
    /** @return list<array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string}> */
    public function health(bool $live = false): array
    {
        $providers = collect(config('ai_images.providers', []))->map(function (array $provider, string $name): array {
            $configured = filled($provider['key'] ?? null) || filled($provider['endpoint'] ?? null);
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

        if ($live) {
            $gemini = app(ImageGenerationProviderInterface::class)->health(true);
            $providers = collect($providers)->map(fn (array $provider): array => $provider['name'] === 'gemini' ? $gemini : $provider)->values()->all();
        }

        return $providers;
    }
}
