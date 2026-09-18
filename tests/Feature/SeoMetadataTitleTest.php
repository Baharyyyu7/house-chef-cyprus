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
}
