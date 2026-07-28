<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\ImageGenerationProviderInterface;
use Illuminate\Support\Facades\Http;

final class GeminiImageCapabilityHealth implements ImageGenerationProviderInterface
{
    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string,http_status:?int,advertised_image_models:int} */
    public function health(bool $live = false): array
    {
        $settings = (array) config('ai_images.providers.gemini', []);
        $key = $settings['key'] ?? null;
        $configured = filled($key);
        $enabled = (bool) ($settings['enabled'] ?? false);
        $base = ['name' => 'gemini', 'configured' => $configured, 'enabled' => $enabled, 'model' => isset($settings['model']) ? (string) $settings['model'] : null, 'http_status' => null, 'advertised_image_models' => 0];

        if (! $configured) {
            return array_merge($base, ['image_capability' => 'disabled_missing_configuration', 'safe_message' => 'Yapılandırma bulunamadı.']);
        }
        if (! $live) {
            return array_merge($base, ['image_capability' => $enabled ? 'enabled_requires_live_test' : 'disabled_pending_cost_approval', 'safe_message' => $enabled ? 'Canlı image capability testi henüz çalıştırılmadı.' : 'Görsel üretimi maliyet onayı beklediği için kapalı.']);
        }

        $response = Http::acceptJson()->timeout(10)->get(rtrim((string) $settings['endpoint'], '/').'/models', ['key' => $key]);
        $status = $response->status();
        if ($status !== 200) {
            $code = match ($status) {
                402 => 'payment_required',
                403 => 'forbidden',
                429 => 'rate_limited',
                default => 'unavailable',
            };

            return array_merge($base, ['http_status' => $status, 'image_capability' => $code, 'safe_message' => 'Canlı capability kontrolü tamamlanamadı; hiçbir görsel üretim çağrısı yapılmadı.']);
        }
        $models = collect($response->json('models', []))->filter(function (array $model): bool {
            $name = strtolower((string) ($model['name'] ?? ''));
            $methods = (array) ($model['supportedGenerationMethods'] ?? []);

            return str_contains($name, 'image') || in_array('generateImages', $methods, true) || in_array('predict', $methods, true);
        });

        return array_merge($base, ['http_status' => $status, 'advertised_image_models' => $models->count(), 'image_capability' => $models->isNotEmpty() ? 'image_models_advertised' : 'no_image_models_advertised', 'safe_message' => 'Model envanteri doğrulandı; bu kontrol görsel üretimi veya ücretlendirme başlatmaz.']);
    }
}
