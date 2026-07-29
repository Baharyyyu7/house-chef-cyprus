<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\RecipeImageProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class BflRecipeImageProvider implements RecipeImageProviderInterface
{
    public function __construct(private readonly ProviderImageResponseParser $parser) {}

    public function name(): string
    {
        return 'bfl';
    }

    public function health(): array
    {
        $settings = (array) config('ai_images.providers.bfl');
        $configured = filled($settings['key'] ?? null);
        $enabled = (bool) ($settings['enabled'] ?? false);

        return ['name' => $this->name(), 'configured' => $configured, 'enabled' => $enabled, 'image_capability' => ! $configured ? 'not_configured' : (! $enabled ? 'disabled_pending_cost_approval' : 'enabled_requires_live_test'), 'model' => $settings['model'] ?? null, 'safe_message' => ! $configured ? 'BFL API anahtarı yapılandırılmadı.' : (! $enabled ? 'BFL canlı üretimi kapalı.' : 'BFL canlı test için hazır; tek çağrı limiti uygulanır.')];
    }

    public function generate(string $prompt, string $negativePrompt, string $aspectRatio): ProviderImageResult
    {
        $settings = (array) config('ai_images.providers.bfl');
        if (! $this->health()['configured'] || ! $this->health()['enabled']) {
            return new ProviderImageResult('provider_blocked', 'IMAGE_PROVIDER_NOT_CONFIGURED', 'BFL canlı üretim için yapılandırılmadı.');
        }
        $dimensions = $aspectRatio === '4:3' ? ['width' => 1024, 'height' => 768] : ['width' => 1024, 'height' => 1024];
        $payload = array_merge(['prompt' => $prompt], $dimensions);
        $started = hrtime(true);
        try {
            $response = Http::withHeaders(['x-key' => (string) $settings['key']])->acceptJson()->contentType('application/json')->timeout(30)
                ->post(rtrim((string) $settings['endpoint'], '/').'/v1/'.rawurlencode((string) $settings['model']), $payload);
        } catch (ConnectionException) {
            return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_TIMEOUT', 'BFL isteği güvenli zaman aşımı sınırını geçti.');
        }
        if (! $response->successful()) {
            return $this->parser->parse($response, strlen((string) json_encode($payload)), $this->elapsed($started), 'bfl');
        }
        $pollingUrl = $response->json('polling_url');
        $requestId = $response->json('id');
        if (! is_string($pollingUrl) || ! $this->isBflUrl($pollingUrl)) {
            return new ProviderImageResult('parser_failed', 'IMAGE_PROVIDER_INVALID_POLLING_URL', 'BFL geçerli bir polling URL döndürmedi.', providerRequestId: is_string($requestId) ? $requestId : null, diagnostic: $this->diagnostic($response, $payload, $started, 'invalid_polling_url'));
        }
        $limit = max(1, min((int) ($settings['poll_limit'] ?? 10), 20));
        for ($attempt = 0; $attempt < $limit; $attempt++) {
            if ($attempt > 0) {
                usleep(max(0, min((int) ($settings['poll_interval_ms'] ?? 500), 2000)) * 1000);
            }
            try {
                $poll = Http::withHeaders(['x-key' => (string) $settings['key']])->acceptJson()->timeout(20)->get($pollingUrl);
            } catch (ConnectionException) {
                return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_TIMEOUT', 'BFL polling isteği güvenli zaman aşımı sınırını geçti.', providerRequestId: is_string($requestId) ? $requestId : null);
            }
            if (! $poll->successful()) {
                return $this->parser->parse($poll, strlen((string) json_encode($payload)), $this->elapsed($started), 'bfl');
            }
            $status = strtolower((string) $poll->json('status'));
            if (in_array($status, ['error', 'failed'], true)) {
                return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_FAILED', 'BFL görsel üretimini tamamlayamadı.', providerRequestId: is_string($requestId) ? $requestId : null, diagnostic: $this->diagnostic($poll, $payload, $started, 'provider_failed'));
            }
            if ($status !== 'ready') {
                continue;
            }
            $sample = $poll->json('result.sample');
            if (! is_string($sample) || ! $this->isBflUrl($sample)) {
                return new ProviderImageResult('parser_failed', 'IMAGE_PROVIDER_INVALID_RESULT_URL', 'BFL geçerli bir sonuç URLsi döndürmedi.', providerRequestId: is_string($requestId) ? $requestId : null, diagnostic: $this->diagnostic($poll, $payload, $started, 'invalid_result_url'));
            }
            try {
                $download = Http::timeout(30)->get($sample);
            } catch (ConnectionException) {
                return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_DOWNLOAD_FAILED', 'BFL sonuç görseli güvenli olarak indirilemedi.', providerRequestId: is_string($requestId) ? $requestId : null);
            }
            $result = $this->parser->parse($download, strlen((string) json_encode($payload)), $this->elapsed($started), 'bfl');

            return new ProviderImageResult($result->status, $result->errorCode, $result->safeMessage, $result->bytes, $result->mime, is_string($requestId) ? $requestId : null, $result->diagnostic);
        }

        return new ProviderImageResult('provider_failed', 'IMAGE_PROVIDER_POLL_TIMEOUT', 'BFL sonucu güvenli polling sınırında hazır olmadı.', providerRequestId: is_string($requestId) ? $requestId : null, diagnostic: $this->diagnostic($response, $payload, $started, 'poll_timeout'));
    }

    private function isBflUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return parse_url($url, PHP_URL_SCHEME) === 'https' && ($host === 'api.bfl.ai' || str_ends_with($host, '.bfl.ai'));
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    private function diagnostic(Response $response, array $payload, int $started, string $parserResult): array
    {
        return ['response_status' => $response->status(), 'content_type' => $response->header('Content-Type'), 'response_body_present' => $response->body() !== '', 'payload_bytes' => strlen($response->body()), 'request_bytes' => strlen((string) json_encode($payload)), 'latency_ms' => $this->elapsed($started), 'billing_status' => 'unknown', 'parser_result' => $parserResult];
    }

    private function elapsed(int $started): int
    {
        return (int) ((hrtime(true) - $started) / 1_000_000);
    }
}
