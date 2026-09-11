<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

/** Builds schemas from already-public, visible editorial data only. */
final class SeoSchemaService
{
    /** @return array<string,mixed> */
    public function recipe(Recipe $recipe, array $ingredients, string $imageUrl = '', ?string $cuisineUrl = null): array
    {
        $isEn = app()->getLocale() === 'en';
        $recipeNode = [
            '@type' => 'Recipe', 'name' => $recipe->title,
            'description' => $recipe->description ?: $recipe->excerpt,
            'url' => route('recipes.show', $recipe),
            'mainEntityOfPage' => route('recipes.show', $recipe),
            'inLanguage' => app()->getLocale(),
            'dateModified' => $recipe->updated_at?->toAtomString(),
            'recipeCuisine' => $recipe->cuisine?->name,
            'recipeCategory' => $recipe->category?->name,
            'prepTime' => 'PT'.$recipe->prep_minutes.'M', 'cookTime' => 'PT'.$recipe->cook_minutes.'M',
            'totalTime' => 'PT'.$recipe->totalMinutes().'M', 'recipeYield' => $recipe->servings.' '.($isEn ? 'servings' : 'porsiyon'),
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $recipe->steps->values()->map(fn ($step, int $index): array => [
                '@type' => 'HowToStep',
                'name' => $step->title,
                'text' => $step->instruction,
                'url' => route('recipes.show', $recipe).'#step-'.($step->position ?: $index + 1),
            ])->all(),
            // author ve datePublished hiçbir tarifte yoktu. İkisi de Google'ın
            // önerdiği alanlar ve author E-E-A-T tarafında doğrudan sayılıyor -
            // sitenin bütün konumlanması "doğrulanmış tarifler" üzerine ama
            // içeriğin arkasında kimin durduğu söylenmiyordu.
            'author' => [
                '@type' => 'Organization',
                'name' => config('brand.name'),
                'url' => route('home'),
            ],
            'datePublished' => $recipe->published_at?->toAtomString(),
        ];
        $crumbs = [['name' => $isEn ? 'Home' : 'Ana Sayfa', 'url' => route('home')]];
        if ($recipe->cuisine !== null && $cuisineUrl !== null) {
            $crumbs[] = ['name' => $isEn ? 'Cuisines' : 'Mutfaklar', 'url' => route('cuisines.index')];
            $crumbs[] = ['name' => $recipe->cuisine->name, 'url' => $cuisineUrl];
        } else {
            $crumbs[] = ['name' => $isEn ? 'Recipes' : 'Tarifler', 'url' => route('recipes.index')];
        }
        $crumbs[] = ['name' => $recipe->title, 'url' => route('recipes.show', $recipe)];

        // Google Recipe zengin sonucu icin image zorunludur. Onayli bir ana
        // gorsel yokken eksik/gecersiz bir Recipe nesnesi ilan etmek yerine
        // sayfanin gercek breadcrumb bilgisini koruyup Recipe nesnesini basma.
        if ($imageUrl === '') {
            return $this->graph(null, $crumbs);
        }

        $recipeNode['image'] = [$imageUrl];

        return $this->graph($recipeNode, $crumbs);
    }

    /** @return array<string,mixed> */
    public function article(BlogPost $post, string $imageUrl = ''): array
    {
        $publisher = [
            '@type' => 'Organization',
            'name' => config('brand.name'),
            'logo' => url(config('brand.assets.mark_light')),
        ];

        $isEn = app()->getLocale() === 'en';
        $article = ['@type' => 'BlogPosting', 'headline' => $post->title, 'description' => $post->excerpt, 'datePublished' => $post->published_at?->toAtomString(), 'dateModified' => $post->updated_at?->toAtomString(), 'mainEntityOfPage' => route('blog.show', $post), 'author' => $publisher, 'publisher' => $publisher, 'inLanguage' => app()->getLocale()];
        if ($imageUrl !== '') {
            $article['image'] = [$imageUrl];
        }
        $graph = $this->graph($article, [
            ['name' => $isEn ? 'Home' : 'Ana Sayfa', 'url' => route('home')],
            ['name' => 'Blog', 'url' => route('blog.index')],
            ['name' => $post->title, 'url' => route('blog.show', $post)],
        ]);
        $faqBlocks = $post->getAttribute('faq_blocks');
        $questions = collect(is_array($faqBlocks) ? $faqBlocks : [])->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))->map(fn (array $faq): array => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]])->values()->all();
        if ($questions !== []) {
            $graph['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $questions];
        }

        return $graph;
    }

    /**
     * @param  iterable<Recipe>  $recipes  Yalnız sayfada gerçekten gösterilen tarifler
     * @param  list<array{name:string,url:string}>  $crumbs
     * @return array<string,mixed>
     */
    public function collectionPage(string $name, string $description, string $url, iterable $recipes, array $crumbs): array
    {
        // Koleksiyon sayfası bir tarif ayrıntı sayfası değildir. ListItem
        // yalnız görünür sıralamayı ve hedef adresi bildirir; Recipe/image
        // ayrıntısı hedef tarif sayfasının kendi şemasının sorumluluğudur.
        $items = collect($recipes)->values()->map(fn (Recipe $recipe, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => route('recipes.show', $recipe),
        ])->all();

        return $this->graph([
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ], $crumbs);
    }

    /**
     * Alt sayfalarini listeleyen ust sayfa.
     *
     * collectionPage() Recipe bekliyor; ust sayfanin ogeleri tarif degil,
     * kendi alt koleksiyon sayfalari. Bos bir ItemList basmak yerine gercek
     * ogeleri bildiriyoruz.
     *
     * @param  list<string>  $urls
     * @param  list<array{name:string,url:string}>  $crumbs
     * @return array<string,mixed>
     */
    public function hubPage(string $name, string $description, string $url, array $urls, array $crumbs): array
    {
        $items = collect($urls)->values()->map(fn (string $adres, int $sira): array => [
            '@type' => 'ListItem',
            'position' => $sira + 1,
            'url' => $adres,
        ])->all();

        return $this->graph([
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ], $crumbs);
    }

    /** @param iterable<BlogPost> $posts @param list<array{name:string,url:string}> $crumbs @return array<string,mixed> */
    public function blogCollectionPage(string $name, string $description, string $url, iterable $posts, array $crumbs): array
    {
        $items = collect($posts)->values()->map(fn (BlogPost $post, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => route('blog.show', $post),
        ])->all();

        return $this->graph([
            '@type' => 'CollectionPage', 'name' => $name, 'description' => $description, 'url' => $url,
            'mainEntity' => ['@type' => 'ItemList', 'numberOfItems' => count($items), 'itemListElement' => $items],
        ], $crumbs);
    }

    /** @param array<string,mixed>|null $node @param list<array{name:string,url:string}> $crumbs @return array<string,mixed> */
    private function graph(?array $node, array $crumbs): array
    {
        $graph = $node === null ? [] : [$node];
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $index): array => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['name'], 'item' => $crumb['url']])->all(),
        ];

        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }
}
