<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Media\EdenAiRecipeImageProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class EdenAiRecipeImageProviderTest extends TestCase
{
    public function test_it_is_disabled_without_a_model_and_key(): void
    {
        config()->set('ai_images.providers.edenai', ['enabled' => true, 'key' => null, 'model' => null, 'endpoint' => 'https://api.edenai.run/v3']);

        $result = app(EdenAiRecipeImageProvider::class)->generate('prompt', 'negative', '4:3');

        $this->assertSame('provider_blocked', $result->status);
        $this->assertSame('IMAGE_PROVIDER_NOT_CONFIGURED', $result->errorCode);
    }

    public function test_it_maps_provider_authentication_errors_without_exposing_response_body(): void
    {
        config()->set('ai_images.generation_enabled', true);
        config()->set('ai_images.providers.edenai', ['enabled' => true, 'key' => 'test-key', 'model' => 'provider-model', 'endpoint' => 'https://api.edenai.run/v3']);
        Http::fake(['api.edenai.run/v3/universal-ai' => Http::response(['detail' => 'sensitive provider detail'], 401)]);

        $result = app(EdenAiRecipeImageProvider::class)->generate('prompt', 'negative', '4:3');

        $this->assertSame('provider_blocked', $result->status);
        $this->assertSame('IMAGE_PROVIDER_UNAUTHORIZED', $result->errorCode);
        $this->assertStringNotContainsString('sensitive', (string) $result->safeMessage);
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer test-key') && $request->url() === 'https://api.edenai.run/v3/universal-ai');
    }
}
