<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Seo\SeoMetadataService;
use Tests\TestCase;

final class SeoMetadataTitleTest extends TestCase
{
    public function test_title_never_severes_brand_into_house_che(): void
    {
        $service = app(SeoMetadataService::class);
        $recipe = (new Recipe(['title' => 'Celeneksel Firinda Agir Pisen Patatesli ve Baharatli Kleftiko Kuzu Tandir', 'slug' => 'kleftiko']))
            ->setRelation('cuisine', null)
            ->setRelation('seoMetadata', null);

        $metadata = $service->for($recipe, $recipe->title, null, url('/tarifler/kleftiko'));

        // Marka ya tam olarak bulunur ya da hiç bulunmaz; asla yarım kalmaz.
        // Uzun başlıkta ek bırakılır, çünkü arama sonucunda asıl değerli
        // olan sorguyu karşılayan kelimelerdir.
        $markaVar = str_contains($metadata['title'], 'House Chef Cyprus');
        $this->assertTrue(! $markaVar || str_ends_with($metadata['title'], '· House Chef Cyprus'));
        $this->assertStringNotContainsString('House Che ·', $metadata['title']);
        $this->assertStringNotContainsString('House Che ', $metadata['title']);
        $this->assertLessThanOrEqual(70, mb_strlen($metadata['title']));
    }

    public function test_title_does_not_duplicate_brand_suffix(): void
    {
        $service = app(SeoMetadataService::class);
        $recipe = (new Recipe(['title' => 'Hellim Kizartmasi', 'slug' => 'hellim-kizartmasi']))
            ->setRelation('cuisine', null)
            ->setRelation('seoMetadata', null);

        $fallbackWithBrand = 'Hellim Kizartmasi · House Chef Cyprus';
        $metadata = $service->for($recipe, $fallbackWithBrand, null, url('/tarifler/hellim-kizartmasi'));

        $count = substr_count($metadata['title'], 'House Chef Cyprus');
        $this->assertSame(1, $count);
    }

    public function test_cyprus_cuisine_adds_kibris_food_descriptor_without_exceeding_length(): void
    {
        $service = app(SeoMetadataService::class);
        $cuisine = new Cuisine(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $recipe = (new Recipe(['title' => 'Hellim Kizartmasi', 'slug' => 'hellim-kizartmasi']))
            ->setRelation('cuisine', $cuisine)
            ->setRelation('seoMetadata', null);

        $metadata = $service->for($recipe, 'Hellim Kizartmasi · House Chef Cyprus', null, url('/tarifler/hellim-kizartmasi'));

        $this->assertStringContainsString('Kıbrıs Yemek Tarifi', $metadata['title']);
        $this->assertStringEndsWith('· House Chef Cyprus', $metadata['title']);
        $this->assertLessThanOrEqual(70, mb_strlen($metadata['title']));
    }

    public function test_turkish_recipe_title_automatically_appends_tarifi_if_missing(): void
    {
        app()->setLocale('tr');
        $service = app(SeoMetadataService::class);
        $recipe = (new Recipe(['title' => 'Mercimek Çorbası', 'slug' => 'mercimek-corbasi']))
            ->setRelation('cuisine', null)
            ->setRelation('seoMetadata', null);

        $metadata = $service->for($recipe, $recipe->title, null, url('/tarifler/mercimek-corbasi'));

        $this->assertStringContainsString('Mercimek Çorbası Tarifi', $metadata['title']);
        $this->assertStringEndsWith('· House Chef Cyprus', $metadata['title']);
    }

    public function test_foreign_locales_append_correct_recipe_intent_keyword(): void
    {
        $service = app(SeoMetadataService::class);
        $recipe = (new Recipe(['title' => 'Mercimek Çorbası', 'slug' => 'mercimek-corbasi']))
            ->setRelation('cuisine', null)
            ->setRelation('seoMetadata', null);

        // English -> Recipe
        app()->setLocale('en');
        $recipe->setRelation('translations', collect([
            new \App\Models\ContentTranslation(['locale' => 'en', 'field' => 'title', 'value' => 'Red Lentil Soup']),
        ]));
        $metaEn = $service->for($recipe, 'Fallback', null, url('/tarifler/mercimek-corbasi'));
        $this->assertStringContainsString('Red Lentil Soup Recipe', $metaEn['title']);

        // German -> Rezept
        app()->setLocale('de');
        $recipe->setRelation('translations', collect([
            new \App\Models\ContentTranslation(['locale' => 'de', 'field' => 'title', 'value' => 'Linsensuppe']),
        ]));
        $metaDe = $service->for($recipe, 'Fallback', null, url('/tarifler/mercimek-corbasi'));
        $this->assertStringContainsString('Linsensuppe Rezept', $metaDe['title']);

        // Polish -> Przepis
        app()->setLocale('pl');
        $recipe->setRelation('translations', collect([
            new \App\Models\ContentTranslation(['locale' => 'pl', 'field' => 'title', 'value' => 'Zupa z soczewicy']),
        ]));
        $metaPl = $service->for($recipe, 'Fallback', null, url('/tarifler/mercimek-corbasi'));
        $this->assertStringContainsString('Zupa z soczewicy Przepis', $metaPl['title']);

        // Portuguese -> Receita
        app()->setLocale('pt');
        $recipe->setRelation('translations', collect([
            new \App\Models\ContentTranslation(['locale' => 'pt', 'field' => 'title', 'value' => 'Sopa de Lentilha']),
        ]));
        $metaPt = $service->for($recipe, 'Fallback', null, url('/tarifler/mercimek-corbasi'));
        $this->assertStringContainsString('Sopa de Lentilha Receita', $metaPt['title']);

        // Russian -> Рецепт
        app()->setLocale('ru');
        $recipe->setRelation('translations', collect([
            new \App\Models\ContentTranslation(['locale' => 'ru', 'field' => 'title', 'value' => 'Чечевичный суп']),
        ]));
        $metaRu = $service->for($recipe, 'Fallback', null, url('/tarifler/mercimek-corbasi'));
        $this->assertStringContainsString('Чечевичный суп Рецепт', $metaRu['title']);

        // Persian -> طرز تهیه
        app()->setLocale('fa');
        $recipe->setRelation('translations', collect([
            new \App\Models\ContentTranslation(['locale' => 'fa', 'field' => 'title', 'value' => 'سوپ دال عدس']),
        ]));
        $metaFa = $service->for($recipe, 'Fallback', null, url('/tarifler/mercimek-corbasi'));
        $this->assertStringContainsString('طرز تهیه', $metaFa['title']);
    }

    public function test_localized_time_and_servings_prefixes(): void
    {
        $service = app(SeoMetadataService::class);
        $recipe = new Recipe([
            'prep_minutes' => 15,
            'cook_minutes' => 30,
            'rest_minutes' => 0,
            'servings' => 4,
        ]);

        $this->assertSame('⏱ 45 dk • 4 Kişilik • ', $service->timeAndServingsPrefix($recipe, 'tr'));
        $this->assertSame('⏱ 45 min • 4 Servings • ', $service->timeAndServingsPrefix($recipe, 'en'));
        $this->assertSame('⏱ 45 Min. • 4 Portionen • ', $service->timeAndServingsPrefix($recipe, 'de'));
        $this->assertSame('⏱ 45 min • 4 porcje • ', $service->timeAndServingsPrefix($recipe, 'pl'));
        $this->assertSame('⏱ 45 min • 4 porções • ', $service->timeAndServingsPrefix($recipe, 'pt'));
        $this->assertSame('⏱ 45 мин • 4 порций • ', $service->timeAndServingsPrefix($recipe, 'ru'));
        $this->assertSame('⏱ 45 دقیقه • 4 نفره • ', $service->timeAndServingsPrefix($recipe, 'fa'));
    }
}
