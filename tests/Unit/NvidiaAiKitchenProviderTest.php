<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AI\NvidiaAiKitchenProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class NvidiaAiKitchenProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('ai_kitchen.providers.nvidia', [
            'enabled' => true,
            'key' => 'nvapi-test-secret',
            'model' => 'nvidia/nemotron-3-nano-30b-a3b',
            'base_url' => 'https://integrate.api.nvidia.com/v1',
            'timeout_seconds' => 12,
            'max_tokens' => 800,
        ]);
    }

    public function test_it_discovers_the_accessible_model_catalog_without_exposing_the_key(): void
    {
        Http::fake([
            'https://integrate.api.nvidia.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'nvidia/nemotron-3-nano-30b-a3b', 'owned_by' => 'nvidia'],
                    ['id' => 'meta/llama-3.2-11b-vision-instruct', 'owned_by' => 'meta'],
                ],
            ]),
        ]);

        $models = app(NvidiaAiKitchenProvider::class)->models();

        $this->assertSame(['meta/llama-3.2-11b-vision-instruct', 'nvidia/nemotron-3-nano-30b-a3b'], array_column($models, 'id'));
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer nvapi-test-secret'));
        $this->assertStringNotContainsString('nvapi-test-secret', json_encode($models, JSON_THROW_ON_ERROR));
    }

    public function test_it_uses_the_configured_openai_compatible_model_contract(): void
    {
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Dolabınızdaki malzemelere göre güvenli öneri.']]],
                'usage' => ['prompt_tokens' => 21, 'completion_tokens' => 9],
            ]),
        ]);

        $result = app(NvidiaAiKitchenProvider::class)->respond([
            'purpose' => 'assistant',
            'question' => 'Ne pişirebilirim?',
            'payload' => ['recipes' => [['title' => 'Mercimek çorbası']]],
            'prompt_version' => 'test-v1',
        ]);

        $this->assertSame('completed', $result['status']);
        $this->assertSame('nvidia/nemotron-3-nano-30b-a3b', $result['model']);
        $this->assertSame(21, $result['input_tokens']);
        $this->assertSame(9, $result['output_tokens']);
        $this->assertSame(0, $result['cost_cents']);
        Http::assertSent(function ($request): bool {
            $payload = $request->data();

            return $payload['model'] === 'nvidia/nemotron-3-nano-30b-a3b'
                && $payload['stream'] === false
                && str_contains($payload['messages'][1]['content'], 'Ne pişirebilirim?');
        });
    }

    public function test_provider_failures_are_classified_without_leaking_credentials_or_raw_body(): void
    {
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'detail' => 'Bearer nvapi-test-secret rejected for /private/path',
            ], 401),
        ]);

        $result = app(NvidiaAiKitchenProvider::class)->respond([
            'purpose' => 'assistant',
            'question' => 'Öneri',
            'payload' => [],
            'prompt_version' => 'test-v1',
        ]);

        $this->assertSame('unavailable', $result['status']);
        $this->assertSame('AI_NVIDIA_AUTH_FAILED', $result['safe_error_code']);
        $this->assertStringNotContainsString('nvapi-test-secret', json_encode($result, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString('/private/path', json_encode($result, JSON_THROW_ON_ERROR));
    }

    public function test_disabled_provider_never_makes_a_remote_request(): void
    {
        config()->set('ai_kitchen.providers.nvidia.enabled', false);
        Http::fake();

        $result = app(NvidiaAiKitchenProvider::class)->respond([
            'purpose' => 'assistant',
            'question' => 'Öneri',
            'payload' => [],
            'prompt_version' => 'test-v1',
        ]);

        $this->assertSame('AI_PROVIDER_DISABLED', $result['safe_error_code']);
        Http::assertNothingSent();
    }
}
