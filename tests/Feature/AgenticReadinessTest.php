<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AgenticReadinessTest extends TestCase
{
    use RefreshDatabase;

    private Recipe $sampleRecipe;

    private Cuisine $cypriotCuisine;

    private Category $mainCategory;

    private Ingredient $halloumi;

    private Ingredient $flour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cypriotCuisine = Cuisine::firstOrCreate(['slug' => 'kibris-mutfagi'], ['name' => 'Kıbrıs Mutfağı', 'public_id' => 'cui_kibris_01']);
        $this->mainCategory = Category::firstOrCreate(['slug' => 'hamur-isi'], ['name' => 'Hamur İşi', 'public_id' => 'cat_hamur_01']);

        $this->halloumi = Ingredient::firstOrCreate(['slug' => 'hellim'], ['name' => 'Hellim', 'public_id' => 'ing_hellim_01', 'is_active' => true]);
        $this->flour = Ingredient::firstOrCreate(['slug' => 'un'], ['name' => 'Un', 'public_id' => 'ing_un_01', 'is_active' => true]);
        $gramUnit = Unit::firstOrCreate(['code' => 'g'], ['name' => 'Gram', 'symbol' => 'g', 'unit_type' => 'mass', 'public_id' => 'unt_gram_01']);

        $this->sampleRecipe = Recipe::create([
            'public_id' => 'rec_hellimli_01',
            'cuisine_id' => $this->cypriotCuisine->id,
            'category_id' => $this->mainCategory->id,
            'title' => 'Kıbrıs Hellimli Börek',
            'slug' => 'kibris-hellimli-borek',
            'excerpt' => 'Geleneksel Kıbrıs hellimli çıtır ev böreği tarifi.',
            'description' => 'Kıbrıs köy mutfağının vazgeçilmez taze nane ve hellimli fırın böreği.',
            'prep_minutes' => 20,
            'cook_minutes' => 30,
            'rest_minutes' => 10,
            'servings' => 4,
            'difficulty' => 'orta',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        RecipeIngredient::create([
            'recipe_id' => $this->sampleRecipe->id,
            'ingredient_id' => $this->halloumi->id,
            'unit_id' => $gramUnit->id,
            'quantity' => 250,
            'display_quantity' => '250',
            'display_measure_code' => 'g',
            'canonical_quantity' => 250,
            'canonical_unit' => 'g',
            'is_required' => true,
            'is_optional' => false,
            'sort_order' => 1,
            'group_name' => 'İç Harç',
        ]);

        RecipeIngredient::create([
            'recipe_id' => $this->sampleRecipe->id,
            'ingredient_id' => $this->flour->id,
            'unit_id' => $gramUnit->id,
            'quantity' => 500,
            'display_quantity' => '500',
            'display_measure_code' => 'g',
            'canonical_quantity' => 500,
            'canonical_unit' => 'g',
            'is_required' => true,
            'is_optional' => false,
            'sort_order' => 2,
            'group_name' => 'Hamur',
        ]);

        RecipeStep::create([
            'recipe_id' => $this->sampleRecipe->id,
            'position' => 1,
            'title' => 'Hamuru Yoğurun',
            'instruction' => 'Unu geniş bir kaba eleyin ve ılık su ekleyerek pürüzsüz kıvama gelene kadar yoğurun.',
            'duration_seconds' => 600,
            'equipment' => 'Yoğurma Kabı',
            'chef_tip' => 'Hamuru 15 dakika dinlendirmek açılmasını kolaylaştırır.',
        ]);

        RecipeStep::create([
            'recipe_id' => $this->sampleRecipe->id,
            'position' => 2,
            'title' => 'Hellimi Rendeleyin ve Fırınlayın',
            'instruction' => 'Hellimi rendeleyip taze nane ile harmanlayın, hamura doldurup 180 derece fırında pişirin.',
            'duration_seconds' => 1800,
            'equipment' => 'Fırın Tepsisi',
        ]);
    }

    /**
     * LEVEL 1: Single-Page Information Extraction
     */
    public function test_level_1_agent_extracts_complete_recipe_details_without_guessing(): void
    {
        $response = $this->get(route('recipes.show', $this->sampleRecipe->slug));
        $response->assertStatus(200);

        // Core metadata
        $response->assertSee('Kıbrıs Hellimli Börek');
        $response->assertSee('Hellim');
        $response->assertSee('Un');
        $response->assertSee('Hamuru Yoğurun');
        $response->assertSee('Hellimi Rendeleyin ve Fırınlayın');

        // Declarative WebMCP tool annotations on recipe page
        $response->assertSee('toolname="adjust_servings"', false);
        $response->assertSee('tooldescription="Adjust recipe portions and scaled ingredient quantities"', false);

        // Accessibility attributes for buttons
        $response->assertSee('aria-label="Porsiyonu azalt"', false);
        $response->assertSee('aria-label="Porsiyonu artır"', false);
    }

    /**
     * LEVEL 2: Single Interaction - Recipe Search via WebMCP API
     */
    public function test_level_2_agent_searches_recipes_via_tool_api(): void
    {
        $response = $this->postJson(route('api.agent.tools.search'), [
            'query' => 'Hellim',
            'cuisine' => 'kibris-mutfagi',
            'max_minutes' => 60,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonFragment(['slug' => 'kibris-hellimli-borek'])
            ->assertJsonStructure([
                'count',
                'total',
                'results' => [
                    '*' => [
                        'title',
                        'slug',
                        'url',
                        'cuisine',
                        'prep_minutes',
                        'cook_minutes',
                        'total_minutes',
                        'servings',
                        'difficulty',
                    ],
                ],
            ]);
    }

    /**
     * LEVEL 2: Single Interaction - Serving Scaling Calculation
     */
    public function test_level_2_agent_adjusts_servings_algorithmically(): void
    {
        // Adjust from 4 servings to 8 servings (2x scaling)
        $response = $this->postJson(route('api.agent.tools.adjust-servings'), [
            'slug' => 'kibris-hellimli-borek',
            'servings' => 8,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('base_servings', 4)
            ->assertJsonPath('target_servings', 8);
        $this->assertEquals(2.0, (float) $response->json('scaling_factor'));

        $ingredients = $response->json('ingredients');
        $this->assertCount(2, $ingredients);
        // 250g * 2 = 500g
        $this->assertEquals('500.0000', $ingredients[0]['quantity']);
        // 500g * 2 = 1000g
        $this->assertEquals('1000.0000', $ingredients[1]['quantity']);
    }

    /**
     * LEVEL 2: Single Interaction - Language Switch Link
     */
    public function test_level_2_agent_switches_language_to_english(): void
    {
        $response = $this->postJson(route('api.agent.tools.switch-language'), [
            'locale' => 'en',
            'path' => '/tarifler/kibris-hellimli-borek',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('locale', 'en')
            ->assertJsonPath('is_supported', true);

        $targetUrl = $response->json('target_url');
        $this->assertStringContainsString('/en/', $targetUrl);
    }

    /**
     * LEVEL 3: Multi-Step Workflow - Step Guidance & Progression
     */
    public function test_level_3_agent_finds_and_navigates_cooking_steps(): void
    {
        // Step 1
        $step1Response = $this->postJson(route('api.agent.tools.cooking-step'), [
            'slug' => 'kibris-hellimli-borek',
            'step' => 1,
        ]);

        $step1Response->assertStatus(200)
            ->assertJsonPath('current_step', 1)
            ->assertJsonPath('total_steps', 2)
            ->assertJsonPath('has_next', true)
            ->assertJsonPath('next_step', 2)
            ->assertJsonPath('title', 'Hamuru Yoğurun');

        // Step 2
        $step2Response = $this->postJson(route('api.agent.tools.cooking-step'), [
            'slug' => 'kibris-hellimli-borek',
            'step' => 2,
        ]);

        $step2Response->assertStatus(200)
            ->assertJsonPath('current_step', 2)
            ->assertJsonPath('has_next', false)
            ->assertJsonPath('next_step', null)
            ->assertJsonPath('title', 'Hellimi Rendeleyin ve Fırınlayın');
    }

    /**
     * LEVEL 4: Multi-Constraint Decision - Pantry Ingredient Matching
     */
    public function test_level_4_agent_finds_recipes_by_pantry_ingredients(): void
    {
        $response = $this->postJson(route('api.agent.tools.find-by-ingredients'), [
            'ingredients' => ['Hellim', 'Un'],
            'max_minutes' => 60,
            'max_missing' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('results.0.slug', 'kibris-hellimli-borek')
            ->assertJsonPath('results.0.missing_count', 0)
            ->assertJsonPath('results.0.matched_count', 2);
    }

    /**
     * LEVEL 5: Consequential Action & Authentication Guardrails
     */
    public function test_level_5_unauthenticated_agent_cannot_save_recipe_without_human_login(): void
    {
        $response = $this->postJson(route('api.agent.tools.save-recipe'), [
            'slug' => 'kibris-hellimli-borek',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('authenticated', false)
            ->assertJsonPath('requires_human_confirmation', true);
    }

    /**
     * LEVEL 5: Authenticated User Can Save Recipe
     */
    public function test_level_5_authenticated_user_can_save_recipe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('api.agent.tools.save-recipe'), [
            'slug' => 'kibris-hellimli-borek',
            'confirmed' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('is_favorited', true);

        $this->assertTrue($user->favoriteRecipes()->where('recipes.id', $this->sampleRecipe->id)->exists());
    }

    public function test_level_5_authenticated_agent_cannot_save_recipe_without_explicit_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('api.agent.tools.save-recipe'), [
            'slug' => 'kibris-hellimli-borek',
        ])->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('requires_human_confirmation', true);

        $this->assertFalse($user->favoriteRecipes()->where('recipes.id', $this->sampleRecipe->id)->exists());
    }

    public function test_agent_tools_do_not_expose_unpublished_or_unverified_recipes(): void
    {
        $draft = $this->sampleRecipe->replicate();
        $draft->public_id = 'rec_draft_01';
        $draft->slug = 'gizli-taslak-tarif';
        $draft->title = 'Gizli Taslak Tarif';
        $draft->status = 'draft';
        $draft->verification_status = 'unreviewed';
        $draft->published_at = null;
        $draft->save();

        foreach ([
            ['api.agent.tools.recipe', ['slug' => $draft->slug]],
            ['api.agent.tools.adjust-servings', ['slug' => $draft->slug, 'servings' => 2]],
            ['api.agent.tools.cooking-step', ['slug' => $draft->slug, 'step' => 1]],
        ] as [$route, $payload]) {
            $this->postJson(route($route), $payload)->assertNotFound();
        }

        $this->actingAs(User::factory()->create())->postJson(route('api.agent.tools.save-recipe'), [
            'slug' => $draft->slug,
            'confirmed' => true,
        ])->assertNotFound();
    }

    /**
     * WebMCP Capability Registry Validation
     */
    public function test_webmcp_capability_registry_returns_all_tools_with_strict_schemas(): void
    {
        $response = $this->getJson(route('api.agent.tools'));

        $response->assertStatus(200)
            ->assertJsonPath('protocol', 'WebMCP/1.0')
            ->assertJsonStructure([
                'protocol',
                'website',
                'base_url',
                'tools' => [
                    '*' => [
                        'name',
                        'description',
                        'parameters',
                        'risk_level',
                    ],
                ],
            ]);

        $toolNames = collect($response->json('tools'))->pluck('name')->all();
        $this->assertContains('search_recipes', $toolNames);
        $this->assertContains('find_recipes_by_ingredients', $toolNames);
        $this->assertContains('get_recipe', $toolNames);
        $this->assertContains('adjust_servings', $toolNames);
        $this->assertContains('get_cooking_step', $toolNames);
        $this->assertContains('switch_language', $toolNames);
        $this->assertContains('save_recipe', $toolNames);

        $saveTool = collect($response->json('tools'))->firstWhere('name', 'save_recipe');
        $this->assertSame(['slug', 'confirmed'], $saveTool['parameters']['required']);
        $this->assertSame('boolean', $saveTool['parameters']['properties']['confirmed']['type']);
    }

    /**
     * Machine Discoverability: llms.txt & /en/llms.txt
     */
    public function test_llms_txt_and_en_llms_txt_expose_agent_tools_section(): void
    {
        $trResponse = $this->get(route('llms.txt'));
        $trResponse->assertStatus(200);
        $trResponse->assertSee('Yapay Zekâ Ajan Araçları ve WebMCP Yetenekleri');
        $trResponse->assertSee('search_recipes');
        $trResponse->assertSee('find_recipes_by_ingredients');
        $trResponse->assertSee('adjust_servings');
        $trResponse->assertSee('POST /api/v1/agent/tools/search');

        $enResponse = $this->get(route('llms.txt.en'));
        $enResponse->assertStatus(200);
        $enResponse->assertSee('AI Agent Tools & WebMCP Capabilities', false);
        $enResponse->assertSee('search_recipes');
        $enResponse->assertSee('find_recipes_by_ingredients');
        $enResponse->assertSee('adjust_servings');
        $enResponse->assertSee('POST /api/v1/agent/tools/search');
    }

    /**
     * Declarative WebMCP Form Attributes on Search & Filter Views
     */
    public function test_views_contain_declarative_toolname_attributes(): void
    {
        $searchPage = $this->get(route('recipes.index'));
        $searchPage->assertStatus(200);
        $searchPage->assertSee('toolname="search_recipes"', false);
        $searchPage->assertSee('tooldescription="Search HouseChef recipes by title, ingredients, cuisine, cooking time, and dietary preferences"', false);

        $explorePage = $this->get(route('ingredients.explore'));
        $explorePage->assertStatus(200);
        $explorePage->assertSee('toolname="find_recipes_by_ingredients"', false);
    }

    /**
     * Locale Switcher Real Semantic Links
     */
    public function test_locale_switcher_exposes_semantic_real_links(): void
    {
        $homePage = $this->get(route('home'));
        $homePage->assertStatus(200);
        $homePage->assertSee('role="menuitem"', false);
        $homePage->assertSee('aria-current="true"', false);
    }
}
