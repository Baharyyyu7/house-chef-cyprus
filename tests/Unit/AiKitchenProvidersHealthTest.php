<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AI\AiKitchenProviderRegistry;
use App\Services\AI\ConfiguredAiKitchenProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AiKitchenProvidersHealthTest extends TestCase
{
    public function test_all_seven_providers_report_healthy_status_and_configured_models(): void
    {
        $health = app(AiKitchenProviderRegistry::class)->health();

        $this->assertCount(7, $health);

        $providers = collect($health)->keyBy('provider');

        $this->assertTrue($providers->has('mock'));
        $this->assertSame('healthy', $providers->get('mock')['status']);
        $this->assertSame('house-chef-local-v1', $providers->get('mock')['model']);

        $this->assertTrue($providers->has('nvidia'));
        $this->assertSame('healthy', $providers->get('nvidia')['status']);
        $this->assertSame('nvidia/nemotron-3-nano-30b-a3b', $providers->get('nvidia')['model']);

        $this->assertTrue($providers->has('gemini'));
        $this->assertSame('healthy', $providers->get('gemini')['status']);
        $this->assertSame('gemini-2.5-flash', $providers->get('gemini')['model']);

        $this->assertTrue($providers->has('openai'));
        $this->assertSame('healthy', $providers->get('openai')['status']);
        $this->assertSame('gpt-4o-mini', $providers->get('openai')['model']);

        $this->assertTrue($providers->has('claude'));
        $this->assertSame('healthy', $providers->get('claude')['status']);
        $this->assertSame('claude-3-5-sonnet-20241022', $providers->get('claude')['model']);

        $this->assertTrue($providers->has('openrouter'));
        $this->assertSame('healthy', $providers->get('openrouter')['status']);
        $this->assertSame('anthropic/claude-3.5-sonnet', $providers->get('openrouter')['model']);

        $this->assertTrue($providers->has('ollama'));
        $this->assertSame('healthy', $providers->get('ollama')['status']);
        $this->assertSame('llama3.2:3b', $providers->get('ollama')['model']);
    }

    public function test_configured_provider_fails_safely_when_key_is_missing(): void
    {
        config()->set('ai_kitchen.providers.gemini.key', null);

        $provider = new ConfiguredAiKitchenProvider('gemini');
        $result = $provider->respond([
            'purpose' => 'assistant',
            'question' => 'Ne yapabilirim?',
            'payload' => [],
        ]);

        $this->assertSame('unavailable', $result['status']);
        $this->assertSame('AI_KEY_NOT_CONFIGURED', $result['safe_error_code']);
    }

    public function test_gemini_provider_executes_api_call_when_key_is_present(): void
    {
        config()->set('ai_kitchen.providers.gemini.key', 'test-gemini-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Gemini test yanıtı.'],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 15,
                    'candidatesTokenCount' => 8,
                ],
            ]),
        ]);

        $provider = new ConfiguredAiKitchenProvider('gemini');
        $result = $provider->respond([
            'purpose' => 'assistant',
            'question' => 'Ne pişirebilirim?',
            'payload' => [],
        ]);

        $this->assertSame('completed', $result['status']);
        $this->assertSame('Gemini test yanıtı.', $result['text']);
        $this->assertSame('gemini-2.5-flash', $result['model']);
        $this->assertSame(15, $result['input_tokens']);
        $this->assertSame(8, $result['output_tokens']);
    }
}
