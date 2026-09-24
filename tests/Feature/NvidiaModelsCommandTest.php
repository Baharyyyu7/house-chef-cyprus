<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class NvidiaModelsCommandTest extends TestCase
{
    public function test_it_reports_the_accessible_catalog_as_json_without_exposing_the_key(): void
    {
        config()->set('ai_kitchen.providers.nvidia.key', 'nvapi-private-value');
        config()->set('ai_kitchen.providers.nvidia.base_url', 'https://integrate.api.nvidia.com/v1');

        Http::fake([
            'https://integrate.api.nvidia.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'nvidia/nemotron-3-super-120b-a12b', 'owned_by' => 'nvidia'],
                    ['id' => 'meta/llama-3.2-11b-vision-instruct', 'owned_by' => 'meta'],
                ],
            ]),
        ]);

        $this->artisan('nvidia:models', ['--json' => true])
            ->expectsOutput('{"provider":"nvidia","count":2,"models":[{"id":"meta/llama-3.2-11b-vision-instruct","owned_by":"meta"},{"id":"nvidia/nemotron-3-super-120b-a12b","owned_by":"nvidia"}]}')
            ->doesntExpectOutputToContain('nvapi-private-value')
            ->assertSuccessful();
    }

    public function test_it_fails_safely_when_no_key_is_configured(): void
    {
        config()->set('ai_kitchen.providers.nvidia.key', '');

        $this->artisan('nvidia:models', ['--json' => true])
            ->expectsOutputToContain('NVIDIA_API_KEY_MISSING')
            ->assertFailed();

        Http::assertNothingSent();
    }
}
