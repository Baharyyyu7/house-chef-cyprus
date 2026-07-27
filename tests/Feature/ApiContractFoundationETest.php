<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApiContractFoundationETest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_lookup_and_recipe_endpoints_return_a_request_id(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('status', 'published')->firstOrFail();
        $ingredient = $recipe->ingredients()->firstOrFail();
        $paths = [
            '/api/v1/health', '/api/v1/status', '/api/v1/version', '/api/v1/recipes',
            '/api/v1/recipes/'.$recipe->public_id, '/api/v1/recipes/'.$recipe->public_id.'/nutrition',
            '/api/v1/recipes/'.$recipe->public_id.'/allergens', '/api/v1/ingredients',
            '/api/v1/ingredients/'.$ingredient->public_id.'/nutrition', '/api/v1/ingredients/'.$ingredient->public_id.'/substitutions',
            '/api/v1/cuisines', '/api/v1/categories', '/api/v1/nutrients', '/api/v1/allergens', '/api/v1/dietary-tags',
        ];

        foreach ($paths as $path) {
            $this->getJson($path)->assertOk()->assertJsonStructure(['data', 'request_id'])->assertHeader('X-Request-ID');
        }
    }

    public function test_recipe_match_rejects_malformed_and_non_json_payloads_with_the_standard_error_envelope(): void
    {
        $this->call('POST', '/api/v1/recipe-match', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{bad json')
            ->assertStatus(400)->assertJsonPath('error.code', 'MALFORMED_REQUEST')->assertJsonStructure(['error' => ['code', 'message', 'details'], 'request_id']);

        $this->post('/api/v1/recipe-match', ['ingredients' => []])
            ->assertStatus(415)->assertJsonPath('error.code', 'UNSUPPORTED_MEDIA_TYPE');
    }

    public function test_not_found_method_validation_and_rate_limit_errors_use_safe_standard_contracts(): void
    {
        $this->getJson('/api/v1/recipes/01J00000000000000000000000')
            ->assertNotFound()->assertJsonPath('error.code', 'RESOURCE_NOT_FOUND')->assertJsonStructure(['request_id']);
        $this->deleteJson('/api/v1/recipes')
            ->assertStatus(405)->assertJsonPath('error.code', 'METHOD_NOT_ALLOWED');
        $this->getJson('/api/v1/recipes?per_page=100')
            ->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR')->assertJsonMissingPath('error.details.id');
    }

    public function test_openapi_checker_reports_all_active_v1_routes_and_machine_readable_output(): void
    {
        $this->artisan('api:openapi-check')->assertSuccessful();
        $this->artisan('api:openapi-check --json')->expectsOutputToContain('"valid":true')->assertSuccessful();
    }

    public function test_recipe_match_uses_the_collection_envelope_for_each_supported_mode(): void
    {
        $this->seed();
        $ingredient = Recipe::query()->where('status', 'published')->firstOrFail()->ingredients()->firstOrFail();

        $this->postJson('/api/v1/recipe-match', ['filters' => ['per_page' => 10]])
            ->assertOk()->assertJsonStructure(['data', 'meta', 'links', 'request_id']);
        $this->postJson('/api/v1/recipe-match', ['ingredients' => [$ingredient->public_id]])
            ->assertOk()->assertJsonStructure(['data', 'meta', 'links', 'request_id']);
    }
}
