<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\RecipeImageProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/** Eden AI universal endpoint adapter; billable work remains budget-gated upstream. */
final class EdenAiRecipeImageProvider implements RecipeImageProviderInterface
{
    public function __construct(private readonly ProviderImageResponseParser $parser) {}

    public function name(): string
    {
        return 'edenai';
    }

    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string} */
    public function health(): array
    {
        $settings = (array) config('ai_images.providers.edenai');
        $configured = filled($settings['key'] ?? null) && filled($settings['model'] ?? null);
        $enabled = (bool) ($settings['enabled'] ?? false);

        return [
            'name' => $this->name(), 'configured' => $configured, 'enabled' => $enabled,
            'image_capability' => ! $configured ? 'not_configured' : (! $enabled ? 'disabled_pending_cost_approval' : 'configured'),
            'model' => filled($settings['model'] ?? null) ? (string) $settings['model'] : null,
            'safe_message' => ! $configured ? 'Eden AI görsel modeli veya anahtarı yapılandırılmadı.' : (! $enabled ? 'Eden AI canlı üretimi bütçe onayı beklediği için kapalı.' : 'Eden AI üretim isteği budget guard üzerinden review-only çalışır.'),
        ];
    }

    public function generate(string $prompt, string $negativePrompt, string $aspectRatio): ProviderImageResult
    {
        $settings = (array) config('ai_images.providers.edenai');
        if (! $this->health()['configured'] || ! $this->health()['enabled']) {
            return new ProviderImageResult('provider_blocked', 'IMAGE_PROVIDER_NOT_CONFIGURED', 'Eden AI canlı üretim için yapılandırılmadı.');
        }
        $payload = [
            'providers' => (string) $settings['model'],
            'text' => $prompt,
            'negative_prompt' => $negativePrompt,
            'resolution' => $aspectRatio === '4:3' ? '1024x768' : '1024x1024',
            'num_images' => 1,
            'response_as_dict' => true,
        ];
        $started = hrtime(true);
        try {
            $response = Http::withToken((string) $settings['key'])
                ->acceptJson()
                ->contentType('application/json')
                ->timeout(45)
                ->post(rtrim((string) $settings['endpoint'], '/').'/universal-ai', $payload);
        } catch (ConnectionException) {
            return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_TIMEOUT', 'Eden AI isteği güvenli zaman aşımı sınırını geçti.');
        }

        return $this->parser->parse($response, strlen((string) json_encode($payload)), (int) ((hrtime(true) - $started) / 1_000_000), $this->name());
    }
}
