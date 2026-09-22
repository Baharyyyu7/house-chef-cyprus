<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeProductJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_ingredient_options_remain_plain_data_on_a_second_request(): void
    {
        $ingredient = Ingredient::query()->create(['name' => 'Test domates', 'slug' => 'test-domates', 'is_active' => true]);
        $this->get(route('home'))->assertOk()->assertSee($ingredient->public_id);
        $cached = cache()->get('home-ingredients-v2:tr');
        $this->assertIsArray($cached);
        $this->assertIsArray($cached[0]);
        $this->get(route('home'))->assertOk()->assertSee('Test domates');
    }

    public function test_home_has_six_primary_sections(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $this->assertSame(6, (new \DOMXPath($dom))->query('//main/section')->length);
    }

    public function test_homepage_renders_the_cuisine_selection_once(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        // Blade çıktıyı kaçırıyor: başlıktaki kesme işareti HTML'de &#039;
        // olarak duruyor. Ham metni aramak her zaman sıfır bulur ve test
        // gerçekte kaç kez basıldığını hiç ölçmemiş olur.
        $this->assertSame(1, substr_count($html, e(__('home.cuisines_title'))));
    }

    public function test_recipe_without_eligible_media_is_not_promoted_on_home(): void
    {
        Recipe::query()->create(['title' => 'Unapproved showcase recipe', 'slug' => 'unapproved-showcase', 'servings' => 2, 'difficulty' => 'easy', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);

        $this->get(route('home'))->assertOk()->assertDontSee('Unapproved showcase recipe');
    }

    public function test_primary_form_submits_ingredient_ids_to_public_matching(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);
        $form = $xpath->query('//form[@id="home-ingredients"]')->item(0);

        $this->assertNotNull($form);
        $this->assertSame(route('ingredients.explore'), $form->getAttribute('action'));
        $this->assertSame(1, $xpath->query('.//select[@name="ingredients[]"]', $form)->length);
        $this->get(route('ingredients.explore'))->assertOk();
    }
}
