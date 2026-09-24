<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\SeoSchemaService;
use Tests\TestCase;

/**
 * Şema servisi kaydedilmemiş (bellek içi) tarifte tabloya dokunmamalı.
 *
 * aggregateRating ve ikame yorumları veritabanı okur; DB'siz akışlarda
 * (factory make(), bellek içi kurulumlar) sorgu patlamamalı, alanlar
 * sessizce atlanmalıdır.
 */
final class RecipeSchemaWithoutDatabaseTest extends TestCase
{
    public function test_in_memory_recipe_renders_schema_without_touching_the_database(): void
    {
        $recipe = (new Recipe(['title' => 'Hellim Tost', 'slug' => 'hellim-tost']))
            ->setRelation('steps', collect())
            ->setRelation('cuisine', null)
            ->setRelation('category', null);

        $this->assertFalse($recipe->exists);

        $node = app(SeoSchemaService::class)->recipe($recipe, ['Hellim'], 'https://example.com/tost.jpg')['@graph'][0];

        $this->assertArrayNotHasKey('aggregateRating', $node);
        $this->assertSame('Recipe', $node['@type']);
    }
}
