<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_endpoints_return_public_data(): void
    {
        $this->seed();
        $recipe = Recipe::first();
        foreach (['/api/v1/health', '/api/v1/recipes', '/api/v1/ingredients', '/api/v1/cuisines', '/api/v1/categories', '/api/v1/recipes/'.$recipe->public_id] as $uri) {
            $this->getJson($uri)->assertOk();
        } $response = $this->getJson('/api/v1/recipes?search=tavuk&max_time=90');
        $response->assertOk()->assertJsonMissing(['id' => $recipe->id]);
    }
}
