<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AffiliateLink;
use App\Models\Category;
use App\Models\Recipe;
use App\Services\Affiliate\RecipeAffiliateMatchingService;
use Database\Seeders\MasterAffiliateCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeAffiliateMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterAffiliateCatalogSeeder::class);
    }

    public function test_matching_service_recommends_blender_for_soup_recipe(): void
    {
        $soupCategory = Category::create([
            'name' => 'Çorba',
            'slug' => 'corba',
            'public_id' => '01TESTCORBA000000000000001',
        ]);

        $recipe = new Recipe([
            'title' => 'Geleneksel Kırmızı Mercimek Çorbası',
            'slug' => 'geleneksel-kirmizi-mercimek-corbasi',
            'description' => 'İpeksi kıvamda süzme mercimek çorbası.',
        ]);
        $recipe->setRelation('category', $soupCategory);

        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forRecipe($recipe, 2);

        $this->assertNotEmpty($matches);
        $this->assertTrue($matches->contains('slug', 'braun-el-blender-seti'));
    }

    public function test_matching_service_recommends_dutch_oven_for_slow_cooked_meat(): void
    {
        $recipe = new Recipe([
            'title' => 'Fırında Kuzu Tandır',
            'slug' => 'firinda-kuzu-tandir',
            'description' => 'Ağır ateşte pişen lokum gibi kuzu eti.',
        ]);

        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forRecipe($recipe, 2);

        $this->assertNotEmpty($matches);
        $this->assertTrue($matches->contains('slug', 'dokum-tencere-lava-28cm'));
    }

    public function test_matching_service_injects_amazon_affiliate_tag(): void
    {
        config()->set('integrations.affiliate.amazon.tag', 'mytesttag-21');

        $recipe = new Recipe([
            'title' => 'Fırında Kuzu Tandır',
            'slug' => 'firinda-kuzu-tandir',
        ]);

        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forRecipe($recipe, 1);

        $this->assertNotEmpty($matches);
        $first = $matches->first();
        $this->assertStringContainsString('tag=mytesttag-21', $first->target_url);
    }

    public function test_matching_service_respects_disabled_flag(): void
    {
        config()->set('integrations.affiliate.enabled', false);

        $recipe = new Recipe(['title' => 'Mercimek Çorbası', 'slug' => 'corba']);
        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forRecipe($recipe);

        $this->assertEmpty($matches);
    }

    public function test_affiliate_redirect_route_increments_click_and_redirects(): void
    {
        $link = AffiliateLink::where('slug', 'dokum-tencere-lava-28cm')->firstOrFail();
        $initialClicks = $link->click_count;

        $response = $this->get(route('affiliate.redirect', ['slug' => $link->slug]));

        $response->assertRedirect($link->target_url);
        $this->assertSame($initialClicks + 1, $link->fresh()->click_count);
    }

    public function test_recipe_page_renders_affiliate_equipment_block(): void
    {
        $category = Category::create([
            'name' => 'Çorba',
            'slug' => 'corba',
            'public_id' => '01TESTCORBA000000000000002',
        ]);

        $recipe = Recipe::create([
            'title' => 'Özel Çorba Testi',
            'slug' => 'ozel-corba-testi',
            'public_id' => '01TESTRECIPE00000000000001',
            'category_id' => $category->id,
            'excerpt' => 'Test çorba özeti.',
            'description' => 'Test çorba açıklaması.',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'rest_minutes' => 0,
            'servings' => 4,
            'difficulty' => 'easy',
        ]);

        $response = $this->get(route('recipes.show', $recipe->slug));

        $response->assertOk();
        $response->assertSee('Şefin Tavsiyesi · Ekipman');
        $response->assertSee('rel="sponsored nofollow noopener"', false);
        $response->assertSee('Braun MultiQuick');
    }
}
