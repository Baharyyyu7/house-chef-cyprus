<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\RecipeImageProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Talks only to an operator-configured, loopback ComfyUI instance. Generated
 * bytes still pass through the shared parser and private media store.
 */
final class ComfyUiRecipeImageProvider implements RecipeImageProviderInterface
{
    public function __construct(private readonly ProviderImageResponseParser $parser) {}

    public function name(): string
    {
        return 'comfyui';
    }

    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string} */
    public function health(): array
    {
        $settings = (array) config('ai_images.providers.comfyui');
        $endpoint = (string) ($settings['endpoint'] ?? '');
        $workflow = (string) ($settings['workflow_path'] ?? '');
        $configured = $this->isLoopbackUrl($endpoint) && is_file($workflow);
        $enabled = (bool) ($settings['enabled'] ?? false);

        return [
            'name' => $this->name(),
            'configured' => $configured,
            'enabled' => $enabled,
            'image_capability' => ! $configured ? 'not_configured' : (! $enabled ? 'disabled_pending_local_setup' : 'enabled_requires_local_service'),
            'model' => isset($settings['model']) ? (string) $settings['model'] : null,
            'safe_message' => ! $this->isLoopbackUrl($endpoint)
                ? 'ComfyUI endpointi yalnız yerel loopback adresi olabilir.'
                : (! is_file($workflow)
                    ? 'ComfyUI workflow dosyası bulunamadı.'
                    : (! $enabled ? 'ComfyUI üretimi yerel kurulum doğrulanana kadar kapalı.' : 'ComfyUI yerel health kontrolü için hazır.')),
        ];
    }

    public function generate(string $prompt, string $negativePrompt, string $aspectRatio): ProviderImageResult
    {
        $health = $this->health();
        if (! $health['configured'] || ! $health['enabled']) {
            return new ProviderImageResult('provider_blocked', 'LOCAL_IMAGE_PROVIDER_NOT_CONFIGURED', 'ComfyUI yerel üretim için yapılandırılmadı.');
        }
        $settings = (array) config('ai_images.providers.comfyui');
        $workflow = $this->workflow($prompt, $negativePrompt, $aspectRatio);
        if ($workflow === null) {
            return new ProviderImageResult('parser_failed', 'LOCAL_IMAGE_WORKFLOW_INVALID', 'ComfyUI workflow dosyası güvenli biçimde okunamadı.');
        }
        $started = hrtime(true);
        try {
            $submitted = Http::acceptJson()->timeout(20)->post($this->url('/prompt'), [
                'prompt' => $workflow,
                'client_id' => (string) Str::uuid(),
            ]);
        } catch (ConnectionException) {
            return new ProviderImageResult('provider_failed', 'LOCAL_IMAGE_SERVICE_UNAVAILABLE', 'Yerel ComfyUI servisine ulaşılamadı.');
        }
        if (! $submitted->successful()) {
            return $this->parser->parse($submitted, strlen((string) json_encode($workflow)), $this->elapsed($started), $this->name());
        }
        $promptId = $submitted->json('prompt_id');
        if (! is_string($promptId) || $promptId === '') {
            return new ProviderImageResult('parser_failed', 'LOCAL_IMAGE_PROMPT_ID_MISSING', 'ComfyUI üretim kimliği döndürmedi.');
        }

        $timeout = max(30, min((int) ($settings['timeout'] ?? 900), 900));
        $interval = max(250, min((int) ($settings['poll_interval_ms'] ?? 2000), 10000));
        $deadline = microtime(true) + $timeout;
        do {
            usleep($interval * 1000);
            try {
                $history = Http::acceptJson()->timeout(20)->get($this->url('/history/'.rawurlencode($promptId)));
            } catch (ConnectionException) {
                return new ProviderImageResult('provider_failed', 'LOCAL_IMAGE_POLL_FAILED', 'ComfyUI üretim durumu okunamadı.', providerRequestId: $promptId);
            }
            if (! $history->successful()) {
                return $this->parser->parse($history, strlen((string) json_encode($workflow)), $this->elapsed($started), $this->name());
            }
            $entry = $history->json($promptId);
            if (! is_array($entry)) {
                continue;
            }
            $status = strtolower((string) data_get($entry, 'status.status', ''));
            if (in_array($status, ['error', 'failed'], true)) {
                return new ProviderImageResult('provider_failed', 'LOCAL_IMAGE_GENERATION_FAILED', 'ComfyUI üretimi tamamlayamadı.', providerRequestId: $promptId, diagnostic: $this->diagnostic($history->status(), $history->header('Content-Type'), $workflow, $started, 'generation_failed'));
            }
            $image = $this->outputImage($entry);
            if ($image === null) {
                continue;
            }
            try {
                $download = Http::timeout(30)->get($this->url('/view'), $image);
            } catch (ConnectionException) {
                return new ProviderImageResult('provider_failed', 'LOCAL_IMAGE_DOWNLOAD_FAILED', 'ComfyUI çıktısı güvenli olarak indirilemedi.', providerRequestId: $promptId);
            }
            $result = $this->parser->parse($download, strlen((string) json_encode($workflow)), $this->elapsed($started), $this->name());

            return new ProviderImageResult($result->status, $result->errorCode, $result->safeMessage, $result->bytes, $result->mime, $promptId, $result->diagnostic);
        } while (microtime(true) < $deadline);

        return new ProviderImageResult('provider_failed', 'LOCAL_IMAGE_TIMEOUT', 'ComfyUI üretimi güvenli zaman aşımı sınırında tamamlanmadı.', providerRequestId: $promptId);
    }

    /** @return array<string,mixed>|null */
    private function workflow(string $prompt, string $negativePrompt, string $aspectRatio): ?array
    {
        $path = (string) config('ai_images.providers.comfyui.workflow_path');
        $contents = @file_get_contents($path);
        $workflow = is_string($contents) ? json_decode($contents, true) : null;
        if (! is_array($workflow)) {
            return null;
        }
        $dimensions = $aspectRatio === '4:3' ? [1024, 768] : [1024, 1024];

        return $this->replaceTokens($workflow, [
            '{{positive_prompt}}' => $prompt,
            '{{negative_prompt}}' => $negativePrompt,
            '{{seed}}' => random_int(1, PHP_INT_MAX),
            '{{width}}' => $dimensions[0],
            '{{height}}' => $dimensions[1],
            '{{model}}' => (string) config('ai_images.providers.comfyui.model'),
        ]);
    }

    /** @param array<string,mixed> $value @param array<string,int|string> $tokens @return array<string,mixed> */
    private function replaceTokens(array $value, array $tokens): array
    {
        array_walk_recursive($value, static function (&$item) use ($tokens): void {
            if (is_string($item) && array_key_exists($item, $tokens)) {
                $item = $tokens[$item];
            }
        });

        return $value;
    }

    /** @param array<string,mixed> $entry @return array<string,string>|null */
    private function outputImage(array $entry): ?array
    {
        foreach ((array) data_get($entry, 'outputs', []) as $output) {
            $image = Arr::first((array) data_get($output, 'images', []));
            if (! is_array($image) || ! is_string($image['filename'] ?? null)) {
                continue;
            }

            return [
                'filename' => (string) $image['filename'],
                'subfolder' => (string) ($image['subfolder'] ?? ''),
                'type' => (string) ($image['type'] ?? 'output'),
            ];
        }

        return null;
    }

    private function isLoopbackUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true) && in_array((string) parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('ai_images.providers.comfyui.endpoint'), '/').$path;
    }

    /** @param array<string,mixed> $workflow @return array<string,mixed> */
    private function diagnostic(int $status, ?string $contentType, array $workflow, int $started, string $parserResult): array
    {
        return [
            'response_status' => $status,
            'content_type' => $contentType,
            'response_body_present' => true,
            'request_bytes' => strlen((string) json_encode($workflow)),
            'latency_ms' => $this->elapsed($started),
            'billing_status' => 'not_applicable',
            'parser_result' => $parserResult,
        ];
    }

    private function elapsed(int $started): int
    {
        return (int) ((hrtime(true) - $started) / 1_000_000);
    }
}
