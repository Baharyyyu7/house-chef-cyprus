<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AiKitchenProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class ConfiguredAiKitchenProvider implements AiKitchenProviderInterface
{
    public function __construct(private readonly string $provider) {}

    public function name(): string
    {
        return $this->provider;
    }

    public function health(): array
    {
        $settings = (array) config("ai_kitchen.providers.{$this->provider}", []);
        $enabled = (bool) ($settings['enabled'] ?? false);
        $model = trim((string) ($settings['model'] ?? ''));
        $configured = $model !== '';

        return [
            'provider' => $this->provider,
            'status' => $enabled && $configured ? 'healthy' : 'disabled',
            'configured' => $configured,
            'model' => $model,
            'billable' => $this->provider !== 'ollama',
        ];
    }

    public function respond(array $request): array
    {
        $settings = (array) config("ai_kitchen.providers.{$this->provider}", []);
        if (! (bool) ($settings['enabled'] ?? false)) {
            return $this->failure('AI_PROVIDER_DISABLED');
        }

        $model = trim((string) ($settings['model'] ?? ''));
        if ($model === '') {
            return $this->failure('AI_MODEL_NOT_CONFIGURED');
        }

        $key = trim((string) ($settings['key'] ?? ''));
        if ($this->provider !== 'ollama' && $key === '') {
            return $this->failure('AI_KEY_NOT_CONFIGURED');
        }

        return match ($this->provider) {
            'gemini' => $this->respondGemini($request, $model, $key, $settings),
            'openai' => $this->respondOpenAiCompatible($request, 'https://api.openai.com/v1', $model, $key, $settings),
            'openrouter' => $this->respondOpenAiCompatible($request, 'https://openrouter.ai/api/v1', $model, $key, $settings),
            'claude' => $this->respondClaude($request, $model, $key, $settings),
            'ollama' => $this->respondOllama($request, $model, (string) ($settings['url'] ?? 'http://127.0.0.1:11434'), $settings),
            default => $this->failure('AI_PROVIDER_NOT_ACTIVATED'),
        };
    }

    /** @param array<string, mixed> $request @param array<string, mixed> $settings */
    private function respondGemini(array $request, string $model, string $key, array $settings): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";
        try {
            $response = Http::acceptJson()->asJson()->timeout((int) ($settings['timeout_seconds'] ?? 12))->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $this->systemPrompt((string) ($request['purpose'] ?? 'assistant'))]],
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $this->userPrompt($request)]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 800,
                ],
            ]);
        } catch (ConnectionException) {
            return $this->failure('AI_GEMINI_CONNECTION_FAILED');
        } catch (Throwable) {
            return $this->failure('AI_GEMINI_REQUEST_FAILED');
        }

        if (! $response->successful()) {
            return $this->failure('AI_GEMINI_REQUEST_FAILED');
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (! is_string($text) || trim($text) === '') {
            return $this->failure('AI_GEMINI_INVALID_RESPONSE');
        }

        return [
            'status' => 'completed',
            'model' => $model,
            'text' => trim($text),
            'input_tokens' => (int) $response->json('usageMetadata.promptTokenCount', 0),
            'output_tokens' => (int) $response->json('usageMetadata.candidatesTokenCount', 0),
            'cost_cents' => 0,
            'safe_error_code' => null,
        ];
    }

    /** @param array<string, mixed> $request @param array<string, mixed> $settings */
    private function respondOpenAiCompatible(array $request, string $baseUrl, string $model, string $key, array $settings): array
    {
        $url = rtrim($baseUrl, '/').'/chat/completions';
        try {
            $response = Http::acceptJson()->asJson()->withToken($key)->timeout((int) ($settings['timeout_seconds'] ?? 12))->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt((string) ($request['purpose'] ?? 'assistant'))],
                    ['role' => 'user', 'content' => $this->userPrompt($request)],
                ],
                'temperature' => 0.2,
                'max_tokens' => 800,
            ]);
        } catch (ConnectionException) {
            return $this->failure('AI_CONNECTION_FAILED');
        } catch (Throwable) {
            return $this->failure('AI_REQUEST_FAILED');
        }

        if (! $response->successful()) {
            return $this->failure('AI_REQUEST_FAILED');
        }

        $text = $response->json('choices.0.message.content');
        if (! is_string($text) || trim($text) === '') {
            return $this->failure('AI_INVALID_RESPONSE');
        }

        return [
            'status' => 'completed',
            'model' => $model,
            'text' => trim($text),
            'input_tokens' => (int) $response->json('usage.prompt_tokens', 0),
            'output_tokens' => (int) $response->json('usage.completion_tokens', 0),
            'cost_cents' => 0,
            'safe_error_code' => null,
        ];
    }

    /** @param array<string, mixed> $request @param array<string, mixed> $settings */
    private function respondClaude(array $request, string $model, string $key, array $settings): array
    {
        $url = 'https://api.anthropic.com/v1/messages';
        try {
            $response = Http::acceptJson()->asJson()
                ->withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                ])
                ->timeout((int) ($settings['timeout_seconds'] ?? 12))
                ->post($url, [
                    'model' => $model,
                    'max_tokens' => 800,
                    'system' => $this->systemPrompt((string) ($request['purpose'] ?? 'assistant')),
                    'messages' => [
                        ['role' => 'user', 'content' => $this->userPrompt($request)],
                    ],
                ]);
        } catch (ConnectionException) {
            return $this->failure('AI_CLAUDE_CONNECTION_FAILED');
        } catch (Throwable) {
            return $this->failure('AI_CLAUDE_REQUEST_FAILED');
        }

        if (! $response->successful()) {
            return $this->failure('AI_CLAUDE_REQUEST_FAILED');
        }

        $text = $response->json('content.0.text');
        if (! is_string($text) || trim($text) === '') {
            return $this->failure('AI_CLAUDE_INVALID_RESPONSE');
        }

        return [
            'status' => 'completed',
            'model' => $model,
            'text' => trim($text),
            'input_tokens' => (int) $response->json('usage.input_tokens', 0),
            'output_tokens' => (int) $response->json('usage.output_tokens', 0),
            'cost_cents' => 0,
            'safe_error_code' => null,
        ];
    }

    /** @param array<string, mixed> $request @param array<string, mixed> $settings */
    private function respondOllama(array $request, string $model, string $url, array $settings): array
    {
        $endpoint = rtrim($url, '/').'/api/chat';
        try {
            $response = Http::acceptJson()->asJson()->timeout((int) ($settings['timeout_seconds'] ?? 12))->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt((string) ($request['purpose'] ?? 'assistant'))],
                    ['role' => 'user', 'content' => $this->userPrompt($request)],
                ],
                'stream' => false,
            ]);
        } catch (ConnectionException) {
            return $this->failure('AI_OLLAMA_CONNECTION_FAILED');
        } catch (Throwable) {
            return $this->failure('AI_OLLAMA_REQUEST_FAILED');
        }

        if (! $response->successful()) {
            return $this->failure('AI_OLLAMA_REQUEST_FAILED');
        }

        $text = $response->json('message.content');
        if (! is_string($text) || trim($text) === '') {
            return $this->failure('AI_OLLAMA_INVALID_RESPONSE');
        }

        return [
            'status' => 'completed',
            'model' => $model,
            'text' => trim($text),
            'input_tokens' => (int) $response->json('prompt_eval_count', 0),
            'output_tokens' => (int) $response->json('eval_count', 0),
            'cost_cents' => 0,
            'safe_error_code' => null,
        ];
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
