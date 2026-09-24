<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GenericRecipeSeoQuarantineTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_published_recipe_with_the_full_generic_step_template_is_removed_from_the_sitemap(): void
    {
        $recipe = $this->recipe('Sahte Şeftali Kebabı', 'sahte-seftali-kebabi', $this->genericSteps());

        $this->artisan('recipes:quarantine-generic-content', ['--apply' => true])->assertSuccessful();

        $this->assertSame('published', $recipe->fresh()->status);
        $this->assertSame('noindex,nofollow', $recipe->fresh()->seoMetadata?->robots);
        $this->get('/sitemaps/recipes.xml')->assertOk()->assertDontSee($recipe->slug, false);
    }

    public function test_without_apply_the_command_only_reports_and_changes_nothing(): void
    {
        $recipe = $this->recipe('Şablon Tarif', 'sablon-tarif', $this->genericSteps());

        $this->artisan('recipes:quarantine-generic-content')->assertSuccessful();

        $this->assertSame('index,follow', $recipe->fresh()->seoMetadata?->robots);
        $this->get('/sitemaps/recipes.xml')->assertOk()->assertSee($recipe->slug, false);
    }

    public function test_a_recipe_with_real_steps_remains_indexable(): void
    {
        $recipe = $this->recipe('Mercimek Çorbası', 'mercimek-corbasi', [
            'Mercimeği soğuk su altında berrak su akana kadar yıkayın.',
            'Soğanı zeytinyağında saydamlaşana kadar kavurun.',
            'Mercimek ve suyu ekleyip taneler dağılana kadar pişirin.',
        ]);

        $this->artisan('recipes:quarantine-generic-content', ['--apply' => true])->assertSuccessful();

        $this->assertSame('index,follow', $recipe->fresh()->seoMetadata?->robots);
        $this->get('/sitemaps/recipes.xml')->assertOk()->assertSee($recipe->slug, false);
    }

    public function test_one_generic_sentence_alone_does_not_quarantine_a_recipe(): void
    {
        $recipe = $this->recipe('Fırın Sebze', 'firin-sebze', [
            $this->genericSteps()[0],
            'Sebzeleri 200 derecelik fırında kenarları kızarana kadar pişirin.',
            'Fırından çıkarınca beş dakika dinlendirip servis edin.',
        ]);

        $this->artisan('recipes:quarantine-generic-content', ['--apply' => true])->assertSuccessful();

        $this->assertSame('index,follow', $recipe->fresh()->seoMetadata?->robots);
    }

    /** @param list<string> $steps */
    private function recipe(string $title, string $slug, array $steps): Recipe
    {
        $recipe = Recipe::query()->create([
            'title' => $title,
            'slug' => $slug,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subWeek(),
        ]);
        $recipe->seoMetadata()->create(['robots' => 'index,follow']);
        $this->makeRecipePubliclyEligible($recipe);

        foreach ($steps as $index => $instruction) {
            $recipe->steps()->create([
                'position' => $index + 1,
                'title' => 'Adım '.($index + 1),
                'instruction' => $instruction,
            ]);
        }

        return $recipe;
    }

    /** @return list<string> */
    private function genericSteps(): array
    {
        return [
            'Malzemeleri yıkayın, doğrayın ve tezgâhta sıralayın.',
            'Tencereyi veya tavayı orta ısıda ısıtın; malzemeleri sırayla ekleyip kıvamını takip edin.',
            'Tadını kontrol edin, kısa süre dinlendirip servis edin.',
        ];
    }
}
