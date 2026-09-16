<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoMetadata;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

final class GeoSeoService
{
    private const CYPRUS_LATITUDE = 35.1690;
    private const CYPRUS_LONGITUDE = 33.3611;
    private const CYPRUS_REGION = 'TR';
    private const CYPRUS_COUNTRY = 'CY';

    public function recipeSchemaWithGeo(Recipe $recipe, array $ingredients, string $imageUrl = '', ?string $cuisineUrl = null): array
    {
        $schema = $this->baseRecipeSchema($recipe, $ingredients, $imageUrl, $cuisineUrl);

        $schema['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => $recipe->geo_latitude ?? self::CYPRUS_LATITUDE,
            'longitude' => $recipe->geo_longitude ?? self::CYPRUS_LONGITUDE,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $recipe->geo_city ?? 'Nicosia',
                'addressRegion' => $recipe->geo_region ?? self::CYPRUS_REGION,
                'addressCountry' => $recipe->geo_country ?? self::CYPRUS_COUNTRY,
            ],
        ];

        $this->addGeoMetadataToSeoMetadata($recipe);

        return $schema;
    }

    public function localBusinessSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'House Chef Cyprus',
            'description' => 'Geleneksel Kıbrıs Mutfağı tarifleri, Kıbrıs Yemekleri ve adaya özgü lezzetler',
            'url' => route('home'),
            'telephone' => config('brand.phone', ''),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => config('brand.address.street', ''),
                'addressLocality' => 'Nicosia',
                'addressRegion' => 'TR',
                'postalCode' => config('brand.address.postal_code', ''),
                'addressCountry' => 'CY',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => self::CYPRUS_LATITUDE,
                'longitude' => self::CYPRUS_LONGITUDE,
            ],
            'openingHours' => config('brand.opening_hours', 'Mo-Sa 09:00-21:00'),
            'priceRange' => config('brand.price_range', '$$'),
            'sameAs' => array_filter([
                config('brand.social.instagram'),
                config('brand.social.facebook'),
                config('brand.social.twitter'),
            ]),
            'containedIn' => [
                '@type' => 'AdministrativeArea',
                'name' => 'Cyprus',
                'addressCountry' => 'CY',
            ],
        ];
    }

    public function hreflangTags(string $currentPath = ''): array
    {
        $locales = Config::get('locales.supported', []);
        $tags = [];

        foreach ($locales as $locale => $config) {
            if (!($config['enabled'] ?? false)) continue;
            $hreflang = $config['html_locale'] ?? $locale;
            $path = $currentPath ?: '/';
            $url = $this->localeUrlFor($locale, $path);
            $tags[] = ['hreflang' => $hreflang, 'href' => $url];
        }

        $tags[] = ['hreflang' => 'x-default', 'href' => $this->localeUrlFor('tr', '/')];

        return $tags;
    }

    public function geoSitemapEntries(iterable $recipes): array
    {
        $entries = [];
        foreach ($recipes as $recipe) {
            $entries[] = [
                'loc' => route('recipes.show', $recipe),
                'geo' => [
                    'geo_name' => $recipe->geo_city ?? 'Nicosia',
                    'geo_region' => $recipe->geo_region ?? 'TR',
                    'geo_country' => $recipe->geo_country ?? 'CY',
                ],
                'changefreq' => 'weekly',
                'priority' => 0.8,
            ];
        }
        return $entries;
    }

    public function addGeoMetadataToSeoMetadata(Recipe|BlogPost $entity): SeoMetadata
    {
        $geoMetadata = [
            'geo_region' => self::CYPRUS_REGION,
            'geo_country' => self::CYPRUS_COUNTRY,
            'geo_city' => 'Nicosia',
            'geo_coordinates' => [
                'latitude' => self::CYPRUS_LATITUDE,
                'longitude' => self::CYPRUS_LONGITUDE,
            ],
            'locale' => 'tr-TR',
            'geo_target' => 'Kıbrıs',
        ];

        $metadata = $entity->seoMetadata()->updateOrCreate([], [
            'geo_metadata' => $geoMetadata,
            'schema_status' => 'geo_updated',
            'last_refreshed_at' => now(),
        ]);

        return $metadata;
    }

    public function geoMetaTitle(string $recipeName): string
    {
        return "Kıbrıs Yemek Tarifleri | {$recipeName}";
    }

    public function geoMetaDescription(string $recipeName): string
    {
        return "Geleneksel Kıbrıs tarifleri: {$recipeName}. Kıbrıs Mutfağı'nın en lezzetli yemek tarifleri ölçülmüş, adımlı ve görselle birlikte.";
    }

    public function updateRecipeGeoFields(Recipe $recipe): void
    {
        if ($recipe->cuisine?->slug === 'kibris-mutfagi') {
            $recipe->update([
                'geo_latitude' => $recipe->geo_latitude ?? self::CYPRUS_LATITUDE,
                'geo_longitude' => $recipe->geo_longitude ?? self::CYPRUS_LONGITUDE,
                'geo_region' => $recipe->geo_region ?? self::CYPRUS_REGION,
                'geo_country' => $recipe->geo_country ?? self::CYPRUS_COUNTRY,
                'geo_city' => $recipe->geo_city ?? 'Nicosia',
            ]);
        }
    }

    public function hreflangHtml(string $currentPath = ''): string
    {
        $tags = $this->hreflangTags($currentPath);
        $html = '';
        foreach ($tags as $tag) {
            $html .= '<link rel="alternate" hreflang="'.$tag['hreflang'].'" href="'.$tag['href'].'" />'."\n";
        }
        return $html;
    }

    public function cyprusHubSchema(string $hubUrl, array $recipeUrls): array
    {
        $items = collect($recipeUrls)->values()->map(fn (string $url, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => $url,
        ])->all();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->localBusinessSchema(),
                [
                    '@type' => 'CollectionPage',
                    'name' => 'Kıbrıs Yemek Tarifleri - Geleneksel Kıbrıs Mutfağı',
                    'description' => 'Geleneksel Kıbrıs yemek tarifleri, Kıbrıs Mutfağı lezzetleri ve adaya özgü tarifler.',
                    'url' => $hubUrl,
                    'mainEntity' => [
                        '@type' => 'ItemList',
                        'numberOfItems' => count($items),
                        'itemListElement' => $items,
                    ],
                ],
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Kıbrıs Yemek Tarifleri', 'item' => $hubUrl],
                    ],
                ],
            ],
        ];
    }

    private function baseRecipeSchema(Recipe $recipe, array $ingredients, string $imageUrl, ?string $cuisineUrl): array
    {
        $locale = app()->getLocale();
        $schemaService = app(SeoSchemaService::class);
        return $schemaService->recipe($recipe, $ingredients, $imageUrl, $cuisineUrl);
    }

    private function localeUrlFor(string $locale, string $path): string
    {
        if ($locale === 'tr') {
            return rtrim(url($path), '/');
        }
        return rtrim(url('/'.$locale.rtrim($path, '/')), '/');
    }
}
