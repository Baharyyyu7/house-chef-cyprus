<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AiKitchenProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class NvidiaAiKitchenProvider implements AiKitchenProviderInterface
{
    public function name(): string
    {
        return 'nvidia';
    }

    /** @return array<string, scalar|bool|null> */
    public function health(): array
    {
        $settings = $this->settings();
        $configured = $this->key() !== '' && $this->model() !== '';

        return [
            'provider' => $this->name(),
            'status' => (bool) ($settings['enabled'] ?? false) && $configured ? 'ready' : ($configured ? 'configured_not_activated' : 'disabled'),
            'configured' => $configured,
            'model' => $this->model(),
            'billable' => true,
        ];
    }

    /** @return list<array{id:string,owned_by:string}> */
    public function models(): array
    {
        if ($this->key() === '') {
            return [];
        }

        $response = $this->client()->get($this->endpoint('/models'));
        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('data', []))
            ->filter(fn (mixed $model): bool => is_array($model) && is_string($model['id'] ?? null))
            ->map(fn (array $model): array => [
                'id' => (string) $model['id'],
                'owned_by' => is_string($model['owned_by'] ?? null) ? $model['owned_by'] : Str::before((string) $model['id'], '/'),
            ])
            ->sortBy('id')
            ->values()
            ->all();
    }

    public function respond(array $request): array
    {
        $settings = $this->settings();
        if (! (bool) ($settings['enabled'] ?? false)) {
            return $this->failure('AI_PROVIDER_DISABLED');
        }
        if ($this->key() === '' || $this->model() === '') {
            return $this->failure('AI_NVIDIA_NOT_CONFIGURED');
        }

        try {
            $response = $this->client()->post($this->endpoint('/chat/completions'), [
                'model' => $this->model(),
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt((string) ($request['purpose'] ?? 'assistant'))],
                    ['role' => 'user', 'content' => $this->userPrompt($request)],
                ],
                'temperature' => 0.2,
                'max_tokens' => max(64, min(2048, (int) ($settings['max_tokens'] ?? 800))),
                'stream' => false,
            ]);
        } catch (ConnectionException) {
            return $this->failure('AI_NVIDIA_CONNECTION_FAILED');
        } catch (Throwable) {
            return $this->failure('AI_NVIDIA_REQUEST_FAILED');
        }

        if (! $response->successful()) {
            return $this->failure(match ($response->status()) {
                401, 403 => 'AI_NVIDIA_AUTH_FAILED',
                408, 429 => 'AI_NVIDIA_RATE_LIMITED',
                default => 'AI_NVIDIA_REQUEST_FAILED',
            });
        }

        $text = $response->json('choices.0.message.content');
        if (! is_string($text) || trim($text) === '') {
            return $this->failure('AI_NVIDIA_INVALID_RESPONSE');
        }

        return [
            'status' => 'completed',
            'model' => $this->model(),
            'text' => trim($text),
            'input_tokens' => max(0, (int) $response->json('usage.prompt_tokens', 0)),
            'output_tokens' => max(0, (int) $response->json('usage.completion_tokens', 0)),
            'cost_cents' => 0,
            'safe_error_code' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function settings(): array
    {
        return (array) config('ai_kitchen.providers.nvidia', []);
    }

    private function key(): string
    {
        return trim((string) ($this->settings()['key'] ?? ''));
    }

    private function model(): string
    {
        return trim((string) ($this->settings()['model'] ?? ''));
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) ($this->settings()['base_url'] ?? 'https://integrate.api.nvidia.com/v1'), '/').$path;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($this->key())
            ->timeout(max(3, min(60, (int) ($this->settings()['timeout_seconds'] ?? 12))));
    }

    private function systemPrompt(string $purpose): string
    {
        return "House Chef Cyprus için {$purpose} yardımcısısınız. Yalnız verilen katalog verilerini kullanın; alerjen, beslenme ve gıda güvenliği konularında belirsizliği açıkça belirtin. İçerik yayınlama veya doğrulama kararı vermeyin. Türkçe, kısa ve uygulanabilir yanıt üretin.";
    }

    /** @param array<string, mixed> $request */
    private function userPrompt(array $request): string
    {
        $safePayload = Arr::except((array) ($request['payload'] ?? []), ['email', 'phone', 'address', 'notes']);
        $context = json_encode($safePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        return 'İstek: '.Str::limit((string) ($request['question'] ?? ''), 1000)."\nKatalog bağlamı: ".Str::limit((string) $context, 12000);
    }

    /** @return array{status:string,text:string,input_tokens:int,output_tokens:int,cost_cents:int,safe_error_code:string} */
    private function failure(string $code): array
    {
        return [
            'status' => 'unavailable',
            'text' => '',
            'input_tokens' => 0,
            'output_tokens' => 0,
            'cost_cents' => 0,
            'safe_error_code' => $code,
        ];
    }
}
